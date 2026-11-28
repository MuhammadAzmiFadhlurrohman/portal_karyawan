<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApprovalDelegation;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\MonthlySchedule;
use App\Models\ShiftSwap;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserSession;
use App\Models\WebAuthnCredential;
use App\Modules\Attendance\Services\LeaveService;
use App\Services\AuditLogger;
use App\Services\DashboardStatsService;
use App\Services\DeviceSessionService;
use App\Services\SystemSettingService;
use App\Services\WebAuthnService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PortalHubController extends Controller
{
    public function index(Request $request, LeaveService $leaveService, DashboardStatsService $dashboardStatsService): View
    {
        /** @var User $user */
        $user = $request->user();

        $stats = $dashboardStatsService->getStats($user);
        $monthlyCalendar = $dashboardStatsService->getMonthlyCalendar($user);

        $hubMetrics = Cache::remember("portal_hub_metrics:user:{$user->id}", 15, function () use ($user, $leaveService) {
            $nowJkt = now('Asia/Jakarta');
            $todayStr = $nowJkt->toDateString();
            $year = (int) $nowJkt->format('Y');
            $month = (int) $nowJkt->format('n');

            $todaySchedule = MonthlySchedule::query()
                ->with(['shift', 'officeLocation'])
                ->where('user_id', $user->id)
                ->whereDate('date', $todayStr)
                ->first();

            $todayAttendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $todayStr)
                ->first();

            $monthlyQuota = SystemSettingService::getInt('leave.monthly_max_days', 3);
            $balance = $leaveService->getOrCreateBalance($user, $year);

            $monthlyUsedLeave = (int) Leave::query()
                ->where('user_id', $user->id)
                ->where('leave_type', 'annual')
                ->whereIn('status', ['pending_supervisor', 'pending_hrd', 'approved'])
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->sum('working_days');

            $monthAttendances = Attendance::query()
                ->where('user_id', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $presentDays = $monthAttendances->whereIn('status', ['present', 'late'])->count();
            $lateMinutes = (int) $monthAttendances->sum('late_minutes');
            $scheduledDays = max(1, MonthlySchedule::query()
                ->where('user_id', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->count());

            // Leave breakdown for this month
            $userLeavesThisMonth = Leave::query()
                ->where('user_id', $user->id)
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->get();
            $cutiCount = $userLeavesThisMonth->where('leave_type', 'annual')->count();
            $izinCount = $userLeavesThisMonth->where('leave_type', 'special')->count();
            $wfhCount = 0; // WFH count in policy
            $totalLeaveCount = $userLeavesThisMonth->count();

            // Total working hours this month (present * standard 8 hrs + approved overtime hours)
            $approvedOvertimeMins = (int) \App\Models\Lembur::query()
                ->where('user_id', $user->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('durasi_menit');
            $totalWorkingHours = ($presentDays * 8) + round($approvedOvertimeMins / 60);

            $pendingHrisApprovals = ShiftSwap::query()
                ->where(function ($q) use ($user): void {
                    $q->where('target_user_id', $user->id)->where('status', 'pending_target');
                })
                ->count()
                + Leave::query()->whereIn('status', ['pending_supervisor', 'pending_hrd'])->count()
                + AttendanceCorrection::query()->whereIn('status', ['pending_supervisor', 'pending_hrd'])->count();

            $shiftCode = $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->code : 'P';
            $shiftName = $todaySchedule && $todaySchedule->shift ? $todaySchedule->shift->name : 'Shift Pagi';
            $officeName = $todaySchedule && $todaySchedule->officeLocation ? $todaySchedule->officeLocation->name : 'Kantor Pusat Bandung / Soreang';
            $radiusM = $todaySchedule && $todaySchedule->officeLocation ? (int) $todaySchedule->officeLocation->radius_m : SystemSettingService::getInt('geofence.default_radius_m', 50);

            return [
                'attendance_today' => [
                    'status' => $todayAttendance ? strtoupper($todayAttendance->status) : 'BELUM CLOCK-IN',
                    'clock_in_time' => $todayAttendance?->clock_in_at?->timezone('Asia/Jakarta')->format('H:i'),
                    'shift_code' => $shiftCode,
                    'shift_label' => "{$shiftName} ({$shiftCode})",
                    'office_name' => $officeName,
                    'geofence_radius_m' => $radiusM,
                ],
                'monthly_attendance' => [
                    'percentage' => min(100.0, round(($presentDays / $scheduledDays) * 100, 1)),
                    'present_days' => $presentDays,
                    'scheduled_days' => $scheduledDays,
                    'late_minutes' => $lateMinutes,
                    'total_working_hours' => $totalWorkingHours,
                ],
                'leave_breakdown' => [
                    'total' => $totalLeaveCount,
                    'cuti' => $cutiCount,
                    'izin' => $izinCount,
                    'wfh' => $wfhCount,
                ],
                'leave_balance' => [
                    'annual_remaining' => $balance->remaining_days,
                    'annual_total' => $balance->annual_quota,
                    'monthly_remaining' => max(0, $monthlyQuota - $monthlyUsedLeave),
                    'monthly_max' => $monthlyQuota,
                ],
                'pending_hris_approvals' => $pendingHrisApprovals,
                'tickets_summary' => [
                    'open' => 2,
                    'proses' => 3,
                    'pending_verifikasi' => 1,
                    'close_today' => 9,
                    'sla_tepat_rate' => 94.2,
                ],
                'gomsn_summary' => [
                    'active_pengajuan' => 2,
                    'pending_realisasi' => 0,
                    'approved_lembur_hours' => 6.5,
                    'active_tasks' => 3,
                ],
            ];
        });

        $effectiveApprover = $user->resolveEffectiveApprover('attendance');
        $activeSessionsCount = UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->count();
        $approvedDevicesCount = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $pendingDevicesCount = $user->can('portal.sessions.manage')
            ? UserDevice::query()->where('status', 'pending')->count()
            : UserDevice::query()->where('user_id', $user->id)->where('status', 'pending')->count();

        return view('portal.hub', [
            'user' => $user,
            'stats' => $stats,
            'metrics' => $hubMetrics,
            'monthlyCalendar' => $monthlyCalendar,
            'effectiveApprover' => $effectiveApprover,
            'activeSessionsCount' => $activeSessionsCount,
            'approvedDevicesCount' => $approvedDevicesCount,
            'pendingDevicesCount' => $pendingDevicesCount,
        ]);
    }

    public function systemSettings(Request $request, WebAuthnService $webAuthnService): View
    {
        $webAuthnService->ensureTableExists();
        $user = $request->user()->load('webauthnCredentials');
        $canManageSystem = $user?->can('portal.settings.manage') || $user?->hasRole(['super_admin', 'admin']);

        if (! $canManageSystem) {
            $fcmService = app(\App\Services\FcmService::class);
            $pref = $fcmService->getUserPreferences($user);

            return view('portal.user-settings', [
                'user' => $user,
                'notificationPreferences' => [
                    'notify_pengajuan' => (bool) $pref->notify_pengajuan,
                    'notify_lembur' => (bool) $pref->notify_lembur,
                    'notify_tiket' => (bool) $pref->notify_tiket,
                    'notify_absen' => (bool) ($pref->notify_absen ?? true),
                ],
            ]);
        }

        // Otomatis isi parameter default jika database parameter masih kosong
        if (SystemSetting::query()->count() === 0) {
            SystemSettingService::ensureDefaults($user?->id);
        }

        $allSettings = SystemSetting::query()
            ->with('updater')
            ->orderBy('group')
            ->orderBy('key')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        $currentSettings = $allSettings->unique('key')->values();
        $historyByKey = $allSettings->groupBy('key');

        return view('portal.settings', [
            'user' => $user,
            'currentSettings' => $currentSettings,
            'historyByKey' => $historyByKey,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password']),
            'password_changed_at' => now(),
        ]);

        AuditLogger::record(
            action: 'auth.password_changed',
            auditable: $user,
            actorId: $user->id
        );

        return back()->with('status', 'Password akun berhasil diperbarui.');
    }

    public function updateQuickReplies(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if ($request->boolean('reset')) {
            $saved = $user->saveQuickReplies(null);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_custom' => false,
                    'message' => 'Pesan cepat berhasil dikembalikan ke default.',
                    'quick_replies' => $saved,
                ]);
            }
            return back()->with('status', 'Pesan cepat berhasil dikembalikan ke default.');
        }

        $validated = $request->validate([
            'quick_replies' => ['required', 'array', 'min:1', 'max:8'],
            'quick_replies.*' => ['nullable'],
        ]);

        $saved = $user->saveQuickReplies($validated['quick_replies']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_custom' => true,
                'message' => 'Daftar pesan cepat berhasil disimpan.',
                'quick_replies' => $saved,
            ]);
        }

        return back()->with('status', 'Daftar pesan cepat berhasil disimpan.');
    }

    public function notificationsInbox(Request $request): View
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $category = trim((string) $request->query('category', 'all'));
        $search = trim((string) $request->query('q', ''));

        // 1. Kumpulkan item "Perlu Tindakan Anda" sesuai role & izin user
        $actionItems = [];
        $isHrdOnly = $user->hasRole('hrd') && ! $user->hasRole(['super_admin', 'admin']);
        $canApprovePengajuan = ! $isHrdOnly && ($user->can('gomsn.pengajuan.approve_atasan') || $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
        $canApproveKoreksi = ! $isHrdOnly && ($user->can('attendance.correction.approve') || $user->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'super_admin', 'admin']));
        $isAtasan = $canApprovePengajuan || $user->can('gomsn.lembur.approve');
        $isFinance = $user->hasRole(['finance', 'admin', 'super_admin']) || $user->can('gomsn.pengajuan.approve_finance');

        if ($canApprovePengajuan) {
            $qPengajuan = \App\Models\Pengajuan::where('status', 'menunggu_atasan')->where('user_id', '!=', $userId);
            if (! $user->hasRole(['super_admin', 'admin'])) {
                $qPengajuan->where('atasan_id', $userId);
            }
            $cnt = $qPengajuan->count();
            if ($cnt > 0) {
                $actionItems[] = [
                    'key' => 'approve_pengajuan',
                    'title' => 'Persetujuan Pengajuan',
                    'desc' => "{$cnt} pengajuan kasbon / pembelian menunggu persetujuan Anda.",
                    'count' => $cnt,
                    'url' => route('gomsn.pengajuan.approvals'),
                    'cta' => 'Tinjau & Setujui',
                    'color' => 'amber',
                    'icon' => 'wallet',
                ];
            }
        }

        if ($isFinance) {
            $finCnt = \App\Models\Pengajuan::where('status', 'menunggu_finance')->count();
            if ($finCnt > 0) {
                $actionItems[] = [
                    'key' => 'finance_disburse',
                    'title' => 'Pencairan Finance',
                    'desc' => "{$finCnt} pengajuan disetujui menunggu proses pencairan dana.",
                    'count' => $finCnt,
                    'url' => route('gomsn.pengajuan.approvals'),
                    'cta' => 'Proses Pencairan',
                    'color' => 'emerald',
                    'icon' => 'wallet',
                ];
            }
        }

        if ($isAtasan) {
            $qLembur = \App\Models\Lembur::where('status', 'pending')->where('user_id', '!=', $userId);
            if (! $user->hasRole(['super_admin', 'admin', 'hrd'])) {
                $qLembur->where(function ($q) use ($user, $userId) {
                    $q->where('atasan_id', $userId);
                    if (! empty($user->division)) {
                        $q->orWhereHas('user', fn ($qu) => $qu->whereRaw('LOWER(division) = ?', [strtolower($user->division)]));
                    }
                });
            }
            $cnt = $qLembur->count();
            if ($cnt > 0) {
                $actionItems[] = [
                    'key' => 'approve_lembur',
                    'title' => 'Persetujuan Lembur',
                    'desc' => "{$cnt} pengajuan lembur tim menunggu persetujuan.",
                    'count' => $cnt,
                    'url' => route('gomsn.overtime.index'),
                    'cta' => 'Buka Lembur',
                    'color' => 'blue',
                    'icon' => 'clock',
                ];
            }
        }

        if ($canApproveKoreksi) {
            $qKor = \App\Models\AttendanceCorrection::whereIn('status', ['pending_supervisor', 'pending_hrd']);
            if (! $user->hasRole(['super_admin', 'admin'])) {
                $qKor->where('user_id', '!=', $userId);
                if (! empty($user->division)) {
                    $qKor->whereHas('user', fn ($qu) => $qu->whereRaw('LOWER(division) = ?', [strtolower($user->division)]));
                }
            }
            $cnt = $qKor->count();
            if ($cnt > 0) {
                $actionItems[] = [
                    'key' => 'approve_koreksi',
                    'title' => 'Koreksi Kehadiran',
                    'desc' => "{$cnt} pengajuan koreksi absen menunggu tinjauan Anda.",
                    'count' => $cnt,
                    'url' => route('attendance.corrections.approvals'),
                    'cta' => 'Periksa Koreksi',
                    'color' => 'teal',
                    'icon' => 'calendar',
                ];
            }
        }

        if ($isHrdOnly || $user->hasRole(['super_admin', 'admin'])) {
            $leaveCnt = \App\Models\Leave::whereIn('status', ['pending_hrd', 'pending_supervisor'])->count();
            if ($leaveCnt > 0) {
                $actionItems[] = [
                    'key' => 'approve_leave',
                    'title' => 'Pengajuan Cuti & Izin',
                    'desc' => "{$leaveCnt} permohonan cuti/izin karyawan menunggu verifikasi HRD.",
                    'count' => $leaveCnt,
                    'url' => route('attendance.leaves.approvals'),
                    'cta' => 'Verifikasi Cuti',
                    'color' => 'purple',
                    'icon' => 'calendar',
                ];
            }
        }

        // Pengajuan milik sendiri yang butuh upload nota / LPJ
        $lpjCnt = \App\Models\Pengajuan::where('user_id', $userId)->where('status', 'menunggu_bukti')->count();
        if ($lpjCnt > 0) {
            $actionItems[] = [
                'key' => 'upload_lpj',
                'title' => 'Upload Nota / LPJ',
                'desc' => "{$lpjCnt} pengajuan Anda sudah cair dan menunggu bukti pengeluaran.",
                'count' => $lpjCnt,
                'url' => route('gomsn.pengajuan.index'),
                'cta' => 'Unggah Nota',
                'color' => 'amber',
                'icon' => 'wallet',
            ];
        }

        // Tiket gangguan aktif yang ditugaskan
        $userRoles = $user->roles->pluck('name')->toArray();
        $isAtasanTeknik = ! empty(array_intersect(['super_admin', 'admin', 'atasan_teknik', 'atasan_umum', 'atasan', 'manager_teknisi'], $userRoles));
        $ticketCnt = $isAtasanTeknik
            ? \App\Models\Tiket::whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                ->where(fn ($q) => $q->doesntHave('ticketAssignees')->orWhereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $userId)))
                ->count()
            : \App\Models\Tiket::whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                ->whereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $userId))
                ->count();
        if ($ticketCnt > 0) {
            $actionItems[] = [
                'key' => 'active_tickets',
                'title' => 'Tiket Gangguan Aktif',
                'desc' => "{$ticketCnt} tiket gangguan sedang aktif dan memerlukan penanganan.",
                'count' => $ticketCnt,
                'url' => route('tickets.index'),
                'cta' => 'Lihat Tiket',
                'color' => 'rose',
                'icon' => 'ticket',
            ];
        }

        // 2. Kumpulkan riwayat notifikasi (tanpa pengingat absen masuk/pulang)
        $logs = \App\Models\NotificationLog::query()
            ->whereNotIn('event_type', ['shift_reminder_clock_in', 'shift_reminder_clock_out'])
            ->where(function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('user_id', $userId)
                        ->where('event_type', '!=', 'broadcast');
                })
                ->orWhere(function ($sub) use ($userId) {
                    $sub->where('event_type', 'broadcast')
                        ->where(function ($b) use ($userId) {
                            $b->whereJsonContains('payload->user_ids', $userId)
                              ->orWhere('payload->target_type', 'all');
                        });
                });
            })
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $now = now('Asia/Jakarta');
        $todayStart = $now->copy()->startOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $weekStart = $now->copy()->subDays(7)->startOfDay();

        $items = $logs->filter(function ($log) {
            $payload = is_array($log->payload) ? $log->payload : [];
            $title = mb_strtolower((string) ($payload['title'] ?? ''));

            return ! str_contains($title, 'pengingat absen masuk')
                && ! str_contains($title, 'pengingat absen pulang');
        })->map(function ($log) use ($todayStart, $yesterdayStart, $weekStart) {
            $payload = is_array($log->payload) ? $log->payload : [];
            $eventType = strtolower((string) ($log->event_type ?: 'info'));
            $cat = match (true) {
                $eventType === 'broadcast' => 'pengumuman',
                str_contains($eventType, 'pengajuan') || str_contains($eventType, 'kasbon') => 'pengajuan',
                str_contains($eventType, 'lembur') || str_contains($eventType, 'overtime') => 'lembur',
                str_contains($eventType, 'tiket') || str_contains($eventType, 'ticket') => 'tiket',
                str_contains($eventType, 'absen') || str_contains($eventType, 'cuti') || str_contains($eventType, 'leave') => 'absensi',
                default => 'lainnya',
            };

            $sentAt = ($log->sent_at ?? $log->created_at)?->copy()->timezone('Asia/Jakarta');
            $timeGroup = 'older';
            if ($sentAt) {
                if ($sentAt->greaterThanOrEqualTo($todayStart)) {
                    $timeGroup = 'today';
                } elseif ($sentAt->greaterThanOrEqualTo($yesterdayStart)) {
                    $timeGroup = 'yesterday';
                } elseif ($sentAt->greaterThanOrEqualTo($weekStart)) {
                    $timeGroup = 'this_week';
                }
            }

            return [
                'id' => $log->id,
                'category' => $cat,
                'event_type' => $eventType,
                'type' => $payload['type'] ?? 'info',
                'title' => $payload['title'] ?? ucfirst(str_replace('_', ' ', $eventType)),
                'body' => $payload['body'] ?? ($payload['message'] ?? ''),
                'link' => $payload['link'] ?? ($payload['deep_link'] ?? null),
                'sender_name' => $payload['sender_name'] ?? ($payload['author'] ?? 'Sistem GoMSN'),
                'sent_at' => $sentAt,
                'sent_human' => $sentAt ? $sentAt->diffForHumans() : '-',
                'sent_formatted' => $sentAt ? $sentAt->format('d M Y, H:i') : '',
                'timestamp' => $sentAt?->timestamp ?? 0,
                'time_group' => $timeGroup,
            ];
        })->values();

        $counts = [
            'all' => $items->count(),
            'pengumuman' => $items->where('category', 'pengumuman')->count(),
            'pengajuan' => $items->where('category', 'pengajuan')->count(),
            'lembur' => $items->where('category', 'lembur')->count(),
            'tiket' => $items->where('category', 'tiket')->count(),
            'absensi' => $items->where('category', 'absensi')->count(),
        ];

        if ($category !== 'all' && $category !== '') {
            $items = $items->where('category', $category)->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $items = $items->filter(function ($it) use ($needle) {
                return str_contains(mb_strtolower($it['title']), $needle)
                    || str_contains(mb_strtolower($it['body']), $needle)
                    || str_contains(mb_strtolower($it['sender_name']), $needle);
            })->values();
        }

        return view('portal.notifications.inbox', [
            'actionItems' => $actionItems,
            'notifications' => $items,
            'counts' => $counts,
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }

    public function updateSystemSetting(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('portal.settings.manage') || $request->user()?->hasRole(['super_admin', 'admin']), 403);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:1000'],
            'effective_from' => ['required', 'date'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        SystemSettingService::set(
            key: $validated['key'],
            value: $validated['value'],
            reason: $validated['change_reason'],
            effectiveFrom: Carbon::parse($validated['effective_from'])
        );

        return back()->with('status', "Parameter `{$validated['key']}` berhasil diperbarui dan dicatat ke jejak audit.");
    }

    public function resetDefaultSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('portal.settings.manage') || $request->user()?->hasRole(['super_admin', 'admin']), 403);

        $inserted = SystemSettingService::ensureDefaults($request->user()?->id);

        return back()->with('status', $inserted > 0 
            ? "Berhasil memulihkan {$inserted} parameter default sistem."
            : "Semua parameter default sistem sudah terdaftar lengkap.");
    }

    public function createSystemSetting(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('portal.settings.manage') || $request->user()?->hasRole(['super_admin', 'admin']), 403);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9_.-]+$/i'],
            'group' => ['required', 'string', 'max:50'],
            'label' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:string,integer,decimal,float,boolean,json'],
            'value' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['required', 'date'],
            'change_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        SystemSettingService::set(
            key: $validated['key'],
            value: $validated['value'],
            reason: $validated['change_reason'],
            effectiveFrom: Carbon::parse($validated['effective_from']),
            group: $validated['group'],
            label: $validated['label'],
            type: $validated['type'],
            description: $validated['description'] ?? null
        );

        return back()->with('status', "Parameter baru `{$validated['key']}` berhasil ditambahkan.");
    }

    public function sessionsAndDevices(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $canManageAll = $user->hasRole(['super_admin', 'admin']) || $user->can('portal.sessions.manage');

        if ($canManageAll) {
            $sessionsQuery = UserSession::query()->with(['user', 'device'])->latest('last_activity_at');
            $devicesQuery = UserDevice::query()->with(['user', 'approver'])->latest('updated_at');
            $delegations = ApprovalDelegation::query()
                ->with(['delegator', 'delegate'])
                ->latest('id')
                ->limit(50)
                ->get();

            $activeSessionsCount = UserSession::query()->whereNull('revoked_at')->count();
            $revokedSessionsCount = UserSession::query()->whereNotNull('revoked_at')->count();
            $totalDevicesCount = UserDevice::query()->count();
        } else {
            $sessionsQuery = UserSession::query()->where('user_id', $user->id)->with(['user', 'device'])->latest('last_activity_at');
            $devicesQuery = UserDevice::query()->where('user_id', $user->id)->with(['user', 'approver'])->latest('updated_at');
            $delegations = ApprovalDelegation::query()
                ->where(function ($q) use ($user) {
                    $q->where('delegator_id', $user->id)->orWhere('delegate_id', $user->id);
                })
                ->with(['delegator', 'delegate'])
                ->latest('id')
                ->limit(20)
                ->get();

            $activeSessionsCount = UserSession::query()->where('user_id', $user->id)->whereNull('revoked_at')->count();
            $revokedSessionsCount = UserSession::query()->where('user_id', $user->id)->whereNotNull('revoked_at')->count();
            $totalDevicesCount = UserDevice::query()->where('user_id', $user->id)->count();
        }

        return view('portal.sessions', [
            'sessions' => $sessionsQuery->limit(200)->get(),
            'devices' => $devicesQuery->limit(200)->get(),
            'delegations' => $delegations,
            'canManageAll' => $canManageAll,
            'activeSessionsCount' => $activeSessionsCount,
            'revokedSessionsCount' => $revokedSessionsCount,
            'totalDevicesCount' => $totalDevicesCount,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'employee_code', 'position']),
        ]);
    }

    public function revokeSession(
        Request $request,
        UserSession $userSession,
        DeviceSessionService $deviceSessionService
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless(
            $actor->id === $userSession->user_id || $actor->can('portal.sessions.manage'),
            403
        );

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $deviceSessionService->revokeSession($userSession, $validated['reason'], $actor->id);

        return back()->with('status', 'Sesi perangkat berhasil dihentikan (Force Logout) dan dicatat pada Audit Log.');
    }

    public function destroySession(Request $request, UserSession $userSession): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless(
            $actor->id === $userSession->user_id || $actor->can('portal.sessions.manage') || $actor->hasRole(['super_admin', 'admin']),
            403
        );

        // Hapus session Laravel jika masih aktif
        \Illuminate\Support\Facades\DB::table('sessions')->where('id', $userSession->session_id)->delete();

        $userName = $userSession->user?->name ?? 'User #'.$userSession->user_id;
        $deviceName = $userSession->device_name;

        AuditLogger::record(
            action: 'session.deleted',
            auditable: $userSession,
            oldValues: [
                'user_id' => $userSession->user_id,
                'device_name' => $deviceName,
                'ip_address' => $userSession->ip_address,
                'status' => $userSession->revoked_at ? 'revoked' : 'active',
            ],
            reason: 'Dihapus oleh Administrator',
            actorId: $actor->id
        );

        $userSession->delete();

        return back()->with('status', "Data sesi login {$userName} ({$deviceName}) berhasil dihapus.");
    }

    public function pruneRevokedSessions(Request $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('portal.sessions.manage') || $actor->hasRole(['super_admin', 'admin']), 403);

        $count = UserSession::query()->whereNotNull('revoked_at')->delete();

        AuditLogger::record(
            action: 'session.pruned_revoked',
            auditable: $actor,
            newValues: ['deleted_count' => $count],
            reason: 'Pembersihan riwayat sesi login yang telah dihentikan',
            actorId: $actor->id
        );

        return back()->with('status', "Berhasil menghapus {$count} riwayat sesi login yang sudah dihentikan.");
    }

    public function destroyDevice(Request $request, UserDevice $userDevice): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('portal.sessions.manage') || $actor->hasRole(['super_admin', 'admin']), 403);

        $name = $userDevice->device_name;
        $userName = $userDevice->user?->name ?? 'User #'.$userDevice->user_id;

        // Invalidate sessions linked to this device
        $sessions = UserSession::query()->where('device_id', $userDevice->id)->get();
        foreach ($sessions as $s) {
            \Illuminate\Support\Facades\DB::table('sessions')->where('id', $s->session_id)->delete();
            $s->delete();
        }

        AuditLogger::record(
            action: 'device.deleted',
            auditable: $userDevice,
            oldValues: ['device_name' => $name, 'user_name' => $userName],
            reason: 'Dihapus oleh Administrator',
            actorId: $actor->id
        );

        $userDevice->delete();

        return back()->with('status', "Perangkat {$name} ({$userName}) berhasil dihapus dari sistem.");
    }

    public function destroyDelegation(Request $request, ApprovalDelegation $delegation): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless(
            $actor->can('portal.sessions.manage') || $actor->hasRole(['super_admin', 'admin']) || $actor->id === $delegation->delegator_id,
            403
        );

        AuditLogger::record(
            action: 'approval_delegation.deleted',
            auditable: $delegation,
            oldValues: ['module' => $delegation->module, 'reason' => $delegation->reason],
            reason: 'Dihapus oleh Administrator / Pemberi Delegasi',
            actorId: $actor->id
        );

        $delegation->delete();

        return back()->with('status', 'Delegasi persetujuan berhasil dihapus.');
    }

    public function approveDevice(Request $request, UserDevice $userDevice): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($actor->can('portal.sessions.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,revoked'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $oldStatus = $userDevice->status;
        $userDevice->update([
            'status' => $validated['status'],
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        AuditLogger::record(
            action: "device.status_{$validated['status']}",
            auditable: $userDevice,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $validated['status']],
            reason: $validated['reason'],
            actorId: $actor->id
        );

        return back()->with('status', "Status perangkat `{$userDevice->device_name}` diubah menjadi `{$validated['status']}`.");
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $validated = $request->validate([
            'delegator_id' => ['required', 'exists:users,id'],
            'delegate_id' => ['required', 'exists:users,id', 'different:delegator_id'],
            'module' => ['required', 'in:all,attendance,ticketing,gomsn'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $actor->can('portal.sessions.manage') && (int) $validated['delegator_id'] !== $actor->id) {
            abort(403, 'Anda hanya dapat membuat delegasi untuk diri sendiri kecuali memiliki hak HRD/Admin.');
        }

        $delegation = ApprovalDelegation::query()->create(array_merge($validated, [
            'is_active' => true,
            'created_by' => $actor->id,
        ]));

        AuditLogger::record(
            action: 'approval_delegation.created',
            auditable: $delegation,
            newValues: $validated,
            reason: $validated['reason'],
            actorId: $actor->id
        );

        return back()->with('status', 'Delegasi persetujuan atasan berhasil disimpan.');
    }

    public function auditLogs(Request $request): View
    {
        abort_unless($request->user()?->can('portal.audit.view'), 403);

        $query = AuditLog::query()->with('user')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action').'%');
        }

        return view('portal.audit-logs', [
            'logs' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function profile(Request $request, WebAuthnService $webAuthnService): View
    {
        $webAuthnService->ensureTableExists();
        $user = $request->user()->load(['supervisor', 'faceTemplate', 'webauthnCredentials']);

        return view('portal.profile', [
            'user' => $user,
        ]);
    }

    public function webauthnRegisterOptions(Request $request, WebAuthnService $webAuthnService): JsonResponse
    {
        $options = $webAuthnService->generateRegistrationOptions($request->user(), $request);

        return response()->json($options);
    }

    public function webauthnRegisterStore(Request $request, WebAuthnService $webAuthnService): JsonResponse
    {
        $validated = $request->validate([
            'credential_id' => ['required', 'string', 'max:255'],
            'client_data_json' => ['required', 'string'],
            'attestation_object' => ['nullable', 'string'],
            'public_key_spki' => ['nullable', 'string'],
            'algorithm' => ['nullable', 'integer'],
            'transports' => ['nullable', 'array'],
            'device_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $credential = $webAuthnService->verifyAndStoreRegistration($request->user(), $request, $validated);

            return response()->json([
                'ok' => true,
                'credential' => [
                    'id' => $credential->id,
                    'credential_id' => $credential->credential_id,
                    'device_name' => $credential->device_name,
                ],
                'message' => 'Biometrik perangkat berhasil diaktifkan.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Gagal mendaftarkan biometrik perangkat.';

            return response()->json(['message' => $msg], 422);
        }
    }

    public function webauthnDestroy(Request $request, WebAuthnCredential $credential): RedirectResponse
    {
        $user = $request->user();
        abort_unless((int) $credential->user_id === (int) $user->id, 403);

        $deviceName = $credential->device_name;
        $credential->delete();

        AuditLogger::record(
            action: 'auth.webauthn_removed',
            auditable: $user,
            oldValues: ['device_name' => $deviceName],
            actorId: $user->id
        );

        return back()->with('status', "Perangkat biometrik ({$deviceName}) berhasil dihapus dari akun Anda.");
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone,' . $user->id],
            'gender' => ['nullable', 'string', 'in:pria,perempuan'],
            'bank' => ['nullable', 'string', 'max:100'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'atas_nama' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $userData = [
            'phone' => $validated['phone'],
            'gender' => $validated['gender'] ?? ($user->gender ?? 'pria'),
            'bank' => $validated['bank'] ?? null,
            'no_rekening' => $validated['no_rekening'] ?? null,
            'atas_nama' => $validated['atas_nama'] ?? null,
        ];

        // Foto profil hanya dapat diubah oleh Administrator di menu Manajemen Data Karyawan
        if ($request->hasFile('foto') && $user->hasRole(['super_admin', 'admin'])) {
            if ($user->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->foto)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->foto);
            }
            $userData['foto'] = $request->file('foto')->store('avatars', 'public');
        }

        if (! empty($validated['new_password'])) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($validated['new_password']);
            $userData['password_changed_at'] = now();
        }

        $user->update($userData);

        AuditLogger::log(
            action: 'profile.updated',
            targetType: 'User',
            targetId: $user->id,
            details: ['updated_fields' => array_keys($userData)],
            actorId: $user->id
        );

        return back()->with('status', 'Profil dan informasi akun Anda berhasil diperbarui.');
    }
}
