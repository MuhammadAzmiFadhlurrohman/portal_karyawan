<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\FcmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NotificationBroadcastController extends Controller
{
    /**
     * Authorize admin or super_admin or HRD
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole(['super_admin', 'admin', 'hrd']) || $user->can('attendance.employee.manage')),
            403,
            'Akses ditolak. Fitur siaran notifikasi hanya untuk Administrator dan HRD.'
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

        // Paginated broadcast logs
        $logs = NotificationLog::query()
            ->where('event_type', 'broadcast')
            ->latest('id')
            ->paginate(15);

        return view('portal.notifications.index', [
            'employees' => $employees,
            'divisionGroups' => $divisionGroups,
            'totalActiveEmployees' => $totalActiveEmployees,
            'registeredDevicesCount' => $registeredDevicesCount,
            'broadcastsTodayCount' => $broadcastsTodayCount,
            'totalBroadcastsCount' => $totalBroadcastsCount,
            'logs' => $logs,
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

        return redirect()->route('portal.notifications.index')
            ->with('status', 'Riwayat siaran notifikasi berhasil dihapus.');
    }
}
