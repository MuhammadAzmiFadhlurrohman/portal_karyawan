<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NotificationBroadcastController extends Controller
{
    /**
     * Authorize admin or super_admin (HRD excluded)
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole(['super_admin', 'admin']) || ($user->can('attendance.employee.manage') && ! $user->hasRole('hrd'))),
            403,
            'Akses ditolak. Fitur siaran notifikasi hanya untuk Administrator.'
        );
    }

    /**
     * Display the notification broadcast center and sending history.
     */
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        // Fetch active employees
        $employees = User::query()
            ->where('is_active', true)
            ->select('id', 'name', 'username', 'employee_code', 'division', 'office', 'position', 'email', 'phone', 'foto', 'updated_at')
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        // Target division groups
        $divisionGroups = [
            'Semua Divisi' => 'all',
            'Teknik Bandung' => 'Teknik Bandung',
            'Teknik Soreang' => 'Teknik Soreang',
            'NOC' => 'NOC',
            'Finance & Akuntansi' => 'Finance & Akuntansi',
            'HRD & Human Capital' => 'HRD & Human Capital',
            'IT & Sistem' => 'IT & Sistem',
            'Executive & Direksi' => 'Direksi',
            'Operasional & Umum' => 'Operasional',
        ];

        // Overall stats
        $totalActiveEmployees = $employees->count();
        $registeredDevicesCount = UserFcmToken::count();
        $broadcastsTodayCount = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->whereDate('created_at', today())
            ->count();
        $totalBroadcastsCount = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->count();

        // Fetch or seed default templates
        $templates = NotificationTemplate::latest('id')->get();
        if ($templates->isEmpty()) {
            $defaultTemplates = [
                [
                    'name' => 'Pengumuman Libur Nasional',
                    'title' => 'Pengumuman Hari Libur & Jadwal Operasional',
                    'body' => 'Diberitahukan kepada seluruh staf dan karyawan bahwa kantor operasional libur dalam rangka Hari Libur Nasional. Untuk jadwal piket shift tetap berjalan normal.',
                    'type' => 'announcement',
                    'target_type' => 'all',
                    'division' => null,
                    'link' => null,
                ],
                [
                    'name' => 'Jadwal Piket & Shift Kerja',
                    'title' => 'Peringatan Jadwal Piket & Shift Kerja Mingguan',
                    'body' => 'Mohon seluruh rekan teknisi dan staf piket memeriksa penempatan jadwal shift dan absensi terbaru pada menu Absensi portal.',
                    'type' => 'warning',
                    'target_type' => 'division',
                    'division' => 'Teknik Bandung',
                    'link' => '/attendance',
                ],
                [
                    'name' => 'Maintenance Jaringan (NOC)',
                    'title' => 'Pemberitahuan Pemeliharaan Jaringan Darurat',
                    'body' => 'Tim NOC akan melakukan maintenance infrastruktur jaringan dan server malam ini. Harap koordinasi dan pantau perkembangan tiket gangguan.',
                    'type' => 'alert',
                    'target_type' => 'division',
                    'division' => 'NOC',
                    'link' => '/tickets',
                ],
                [
                    'name' => 'Cut-Off Pengajuan Dana & Lembur',
                    'title' => 'Reminder Batas Akhir Pengajuan Dana & Lembur Bulanan',
                    'body' => 'Diingatkan kepada seluruh karyawan untuk segera menyelesaikan input dan pengajuan dana operasional maupun lembur sebelum cut-off akhir bulan.',
                    'type' => 'info',
                    'target_type' => 'all',
                    'division' => null,
                    'link' => '/gomsn/pengajuan',
                ],
            ];

            foreach ($defaultTemplates as $dt) {
                NotificationTemplate::create($dt);
            }

            $templates = NotificationTemplate::latest('id')->get();
        }

        return view('portal.notifications.index', [
            'employees' => $employees,
            'divisionGroups' => $divisionGroups,
            'totalActiveEmployees' => $totalActiveEmployees,
            'registeredDevicesCount' => $registeredDevicesCount,
            'broadcastsTodayCount' => $broadcastsTodayCount,
            'totalBroadcastsCount' => $totalBroadcastsCount,
            'templates' => $templates,
        ]);
    }

    /**
     * Display the dedicated broadcast notification history page.
     */
    public function history(Request $request): View
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->input('search', ''));
        $type = trim((string) $request->input('type', ''));
        $date = trim((string) $request->input('date', ''));

        $query = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->with('user:id,name,username,foto')
            ->latest('id');

        // Search filter (keyword in title, body, or recipient)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Type filter (info, announcement, warning, alert)
        if ($type !== '' && in_array($type, ['info', 'announcement', 'warning', 'alert'], true)) {
            $query->where('payload', 'like', '%"type":"' . $type . '"%');
        }

        // Date filter
        if ($date !== '') {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(15)->withQueryString();

        $totalBroadcastsCount = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->count();
        $broadcastsTodayCount = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->whereDate('created_at', today())
            ->count();
        $totalActiveEmployees = User::where('is_active', true)->count();

        return view('portal.notifications.history', [
            'logs' => $logs,
            'totalBroadcastsCount' => $totalBroadcastsCount,
            'broadcastsTodayCount' => $broadcastsTodayCount,
            'totalActiveEmployees' => $totalActiveEmployees,
            'currentSearch' => $search,
            'currentType' => $type,
            'currentDate' => $date,
        ]);
    }

    /**
     * Send broadcast notification to selected target or all users.
     */
    public function send(Request $request, FcmService $fcmService): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'target_type' => ['required', 'string', 'in:all,selected,division'],
            'division' => ['nullable', 'string', 'max:100'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:info,announcement,warning,alert'],
            'link' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Judul notifikasi wajib diisi.',
            'title.max' => 'Judul notifikasi maksimal 120 karakter.',
            'body.required' => 'Isi pesan notifikasi wajib diisi.',
            'body.max' => 'Isi pesan notifikasi maksimal 1000 karakter.',
            'type.required' => 'Tipe notifikasi wajib dipilih.',
            'target_type.required' => 'Target penerima notifikasi wajib dipilih.',
        ]);

        $targetType = $validated['target_type'];
        $recipients = collect();
        $recipientSummary = '';

        if ($targetType === 'all') {
            $recipients = User::query()
                ->where('is_active', true)
                ->get();
            $recipientSummary = 'Semua Karyawan (' . $recipients->count() . ' orang)';
        } elseif ($targetType === 'division') {
            $divInput = trim((string) ($validated['division'] ?? ''));
            if (empty($divInput)) {
                return back()->withInput()->with('error', 'Silakan pilih divisi target penerima.');
            }

            $query = User::query()->where('is_active', true);
            if ($divInput === 'Teknik Bandung') {
                $query->where('division', 'Teknik')
                      ->where(function ($q) {
                          $q->whereNull('office')->orWhere('office', '!=', 'soreang');
                      });
            } elseif ($divInput === 'Teknik Soreang') {
                $query->where('division', 'Teknik')
                      ->where('office', 'soreang');
            } elseif ($divInput === 'NOC') {
                $query->where('division', 'NOC');
            } elseif ($divInput === 'Direksi') {
                $query->whereIn('division', ['Direksi', 'Executive']);
            } elseif ($divInput === 'Finance & Akuntansi') {
                $query->where(function ($q) {
                    $q->where('division', 'like', '%Finance%')
                      ->orWhere('division', 'like', '%Akuntansi%');
                });
            } elseif ($divInput === 'HRD & Human Capital') {
                $query->where(function ($q) {
                    $q->where('division', 'like', '%HRD%')
                      ->orWhere('division', 'like', '%Human Capital%');
                });
            } else {
                $query->where('division', 'like', "%{$divInput}%");
            }

            $recipients = $query->get();
            $recipientSummary = 'Divisi ' . $divInput . ' (' . $recipients->count() . ' orang)';
        } elseif ($targetType === 'selected') {
            $userIds = array_filter(array_map('intval', (array) ($validated['user_ids'] ?? [])));
            if (empty($userIds)) {
                return back()->withInput()->with('error', 'Pilih minimal satu karyawan sebagai target penerima.');
            }

            $recipients = User::query()
                ->where('is_active', true)
                ->whereIn('id', $userIds)
                ->get();

            if ($recipients->isEmpty()) {
                return back()->withInput()->with('error', 'Tidak ada karyawan aktif yang cocok dengan pilihan Anda.');
            }

            $namesPreview = $recipients->take(3)->pluck('name')->join(', ');
            $recipientSummary = $recipients->count() . ' Karyawan (' . $namesPreview . ($recipients->count() > 3 ? '...' : '') . ')';
        }

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'Target penerima notifikasi kosong atau tidak ditemukan.');
        }

        // Dispatch Web / Mobile Push Notification via FCM
        $fcmSentCount = 0;
        $deepLink = !empty($validated['link']) ? $validated['link'] : url('/');

        try {
            $fcmSentCount = $fcmService->sendToUsers(
                $recipients->pluck('id')->toArray(),
                'broadcast',
                $validated['title'],
                $validated['body'],
                $deepLink,
                [
                    'type' => $validated['type'],
                    'category' => 'announcement',
                    'author' => $request->user()->name,
                    'author_id' => (string) $request->user()->id,
                    'tag' => 'broadcast-' . time(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('FCM Broadcast Notification Error: ' . $e->getMessage());
        }

        // Log the broadcast event in notification_logs
        NotificationLog::query()->create([
            'user_id' => $request->user()->id,
            'channel' => 'fcm_web_push',
            'event_type' => 'broadcast',
            'recipient' => $recipientSummary,
            'payload' => [
                'title' => $validated['title'],
                'body' => $validated['body'],
                'type' => $validated['type'],
                'link' => $validated['link'] ?? null,
                'sender_id' => $request->user()->id,
                'sender_name' => $request->user()->name,
                'target_type' => $targetType,
                'target_count' => $recipients->count(),
                'push_sent_count' => $fcmSentCount,
                'user_ids' => $recipients->pluck('id')->toArray(),
                'recipient_preview' => $recipients->take(15)->pluck('name')->values()->toArray(),
            ],
            'status' => 'sent',
            'attempts' => 1,
            'sent_at' => now(),
        ]);

        return redirect()->route('portal.notifications.index')
            ->withInput()
            ->with('status', "Notifikasi '{$validated['title']}' berhasil dikirimkan ke {$recipients->count()} karyawan!");
    }

    /**
     * Delete a notification log entry.
     */
    public function destroy(Request $request, NotificationLog $notificationLog): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $notificationLog->delete();

        return back()->with('status', 'Riwayat siaran notifikasi berhasil dihapus.');
    }

    /**
     * Save current form message as a reusable template.
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:info,announcement,warning,alert'],
            'target_type' => ['nullable', 'string', 'in:all,selected,division'],
            'division' => ['nullable', 'string', 'max:100'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'link' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama template wajib diisi.',
            'title.required' => 'Judul notifikasi wajib diisi.',
            'body.required' => 'Isi pesan wajib diisi.',
        ]);

        $template = NotificationTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'target_type' => $validated['target_type'] ?? 'all',
            'division' => $validated['division'] ?? null,
            'user_ids' => !empty($validated['user_ids']) ? array_values(array_map('intval', $validated['user_ids'])) : null,
            'link' => $validated['link'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Template '{$template->name}' berhasil disimpan!",
            'template' => $template,
        ]);
    }

    /**
     * Delete a saved template.
     */
    public function deleteTemplate(Request $request, NotificationTemplate $template): JsonResponse
    {
        $this->authorizeAdmin($request);

        $name = $template->name;
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => "Template '{$name}' berhasil dihapus.",
        ]);
    }
}
