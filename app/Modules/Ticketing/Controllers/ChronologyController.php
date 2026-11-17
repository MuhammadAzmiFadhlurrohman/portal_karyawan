<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TicketChronology;
use App\Models\Tiket;
use App\Modules\Ticketing\Services\ChronologyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChronologyController extends Controller
{
    public function __construct(
        protected ChronologyService $chronologyService,
    ) {}

    /**
     * Store append-only chronology entry (§7.3, AC-05).
     */
    public function store(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        // AC-05: Non-assigned technician write attempt is rejected with 403!
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'category' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'reply_to_id' => ['nullable', 'integer'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'gps_accuracy' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:20480'], // 20MB
        ]);

        $category = ! empty($validated['category']) ? strtoupper($validated['category']) : 'LAIN';
        if (! in_array($category, ['IZIN', 'OTDR', 'TRACING', 'MATERIAL', 'JOINTING', 'LINK_UP', 'SELESAI', 'LAIN', 'INVESTIGASI'])) {
            $category = 'LAIN';
        }

        $message = trim((string) ($request->input('message') ?? ''));
        if ($message === '') {
            if (! empty($validated['latitude']) && ! empty($validated['longitude'])) {
                $message = '📍 Lokasi Titik Lapangan terlampir';
            } elseif ($request->hasFile('photo') || $request->hasFile('video')) {
                $message = '📷 Lampiran dokumentasi lapangan';
            } else {
                $message = 'Update koordinasi lapangan';
            }
        }

        // Clamp gps_accuracy between 0 and 9999.99 to prevent MySQL decimal(6,2) 1264 Out of range error
        $gpsAccuracy = null;
        if (isset($validated['gps_accuracy']) && is_numeric($validated['gps_accuracy'])) {
            $gpsAccuracy = min(9999.99, max(0, (float) $validated['gps_accuracy']));
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('tickets/chronologies/photos', 'public');
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('tickets/chronologies/videos', 'public');
        }

        $replyToId = $request->filled('reply_to_id') ? (int) $request->input('reply_to_id') : null;
        $replySender = $request->input('reply_sender');
        $replySnippet = $request->input('reply_snippet');

        // Fallback for environments where migration hasn't been executed
        if ($replyToId && $replySender && ! \Illuminate\Support\Facades\Schema::hasColumn('ticket_chronologies', 'reply_to_id')) {
            if (! str_starts_with($message, '[Balas @')) {
                $snippetClean = str_replace(["\r", "\n", '|', ']'], ' ', \Illuminate\Support\Str::limit((string) $replySnippet, 80));
                $message = "[Balas @{$replySender} | {$snippetClean}]: ".$message;
            }
        }

        try {
            $entry = $this->chronologyService->addChronology(
                tiket: $tiket,
                user: $request->user(),
                category: $category,
                message: $message,
                photoPath: $photoPath,
                videoPath: $videoPath,
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                gpsAccuracy: $gpsAccuracy,
                replyToId: $replyToId,
            );

            if ($request->wantsJson()) {
                $entry->load(['user', 'replyTo.user']);
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan terkirim',
                    'chronology' => $entry,
                    'sender_badge' => $entry->user?->chat_badge ?? 'Staf',
                    'sender_avatar' => $entry->user?->avatar_url,
                    'sender_initials' => $entry->user?->initials ?? 'U',
                    'photo_url' => $entry->photo_url,
                    'video_url' => $entry->video_url,
                ]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', 'Kronologis berhasil ditambahkan.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['chronology' => $e->getMessage()]);
        }
    }

    /**
     * Update chronology message (§WhatsApp message edit).
     */
    public function update(Request $request, TicketChronology $chronology): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && ($user->hasRole('admin') || ($user->role ?? '') === 'admin');
        if (! $user || ($user->id !== $chronology->user_id && ! $isAdmin)) {
            abort(403, 'Anda tidak memiliki hak untuk mengedit pesan ini.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $chronology->update([
            'message' => trim($validated['message']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil diperbarui.',
                'chronology' => $chronology,
            ]);
        }

        return redirect()->back()->with('success', 'Pesan berhasil diperbarui.');
    }

    /**
     * Delete chronology entry (§WhatsApp message delete).
     */
    public function destroy(Request $request, TicketChronology $chronology): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user && ($user->hasRole('admin') || ($user->role ?? '') === 'admin');
        if (! $user || ($user->id !== $chronology->user_id && ! $isAdmin)) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus pesan ini.');
        }

        if ($chronology->photo_path && Storage::disk('public')->exists($chronology->photo_path)) {
            Storage::disk('public')->delete($chronology->photo_path);
        }
        if ($chronology->video_path && Storage::disk('public')->exists($chronology->video_path)) {
            Storage::disk('public')->delete($chronology->video_path);
        }

        $chronology->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Server-Sent Events (SSE) live chat stream for real-time synchronization across accounts.
     */
    public function stream(Request $request, Tiket $tiket): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        Gate::authorize('view', $tiket);

        $lastId = (int) $request->query('last_id', 0);
        $currentUserId = $request->user()?->id;

        // Release the session lock immediately so concurrent requests (like POSTing a message) are not blocked!
        if (session()->isStarted()) {
            session()->save();
        }

        return response()->stream(function () use ($tiket, $lastId, $currentUserId) {
            // Disable output buffering for real-time flush
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $currentLastId = $lastId;
            $startTime = time();

            // Run loop for up to 25 seconds (safe for PHP max execution and Nginx timeout)
            while (time() - $startTime < 25) {
                if (connection_aborted()) {
                    break;
                }

                $newChronologies = TicketChronology::with(['user', 'replyTo.user'])
                    ->where('tiket_id', $tiket->id)
                    ->where('id', '>', $currentLastId)
                    ->orderBy('id', 'asc')
                    ->get();

                if ($newChronologies->isNotEmpty()) {
                    foreach ($newChronologies as $chron) {
                        $currentLastId = max($currentLastId, $chron->id);
                        $isSelf = $currentUserId && $chron->user_id === $currentUserId;

                        $payload = [
                            'id' => $chron->id,
                            'tiket_id' => $chron->tiket_id,
                            'user_id' => $chron->user_id,
                            'sender_name' => $isSelf ? 'Anda' : ($chron->user?->name ?? 'User'),
                            'is_self' => $isSelf,
                            'sender_badge' => $chron->user?->chat_badge ?? ($chron->user?->division ?: 'Petugas'),
                            'sender_avatar' => $chron->user?->avatar_url,
                            'sender_initials' => $chron->user?->initials ?? 'U',
                            'message' => $chron->message,
                            'category' => $chron->category ?? 'LAIN',
                            'time_str' => $chron->created_at ? $chron->created_at->format('H:i') . ' WIB' : '',
                            'date_str' => $chron->created_at ? $chron->created_at->format('d M Y, H:i') . ' WIB' : '',
                            'latitude' => $chron->latitude ? (float) $chron->latitude : null,
                            'longitude' => $chron->longitude ? (float) $chron->longitude : null,
                            'photo_url' => $chron->photo_url,
                            'video_url' => $chron->video_url,
                            'reply_to_id' => $chron->reply_to_id,
                            'reply_sender' => $chron->replyTo?->user?->name,
                            'reply_snippet' => $chron->replyTo ? ($chron->replyTo->message ?: ($chron->replyTo->latitude ? '📍 Titik Lokasi Lapangan' : '📷 Lampiran Foto')) : null,
                        ];

                        echo "event: new_message\n";
                        echo "data: " . json_encode($payload) . "\n\n";
                        flush();
                    }
                }

                // Send periodic heartbeat comment to keep connection alive
                echo ": keepalive\n\n";
                flush();

                usleep(1500000); // Poll every 1.5s
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Polling fallback endpoint for chat synchronization.
     */
    public function poll(Request $request, Tiket $tiket): JsonResponse
    {
        Gate::authorize('view', $tiket);

        $lastId = (int) $request->query('last_id', 0);
        $currentUserId = $request->user()?->id;

        // Auto mark incoming unread messages as read when viewing/polling this ticket
        if ($currentUserId) {
            TicketChronology::where('tiket_id', $tiket->id)
                ->where('user_id', '!=', $currentUserId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        // Get max read ID for self messages (up to which our own messages have been read by others)
        $maxReadSelfId = (int) TicketChronology::where('tiket_id', $tiket->id)
            ->where('user_id', $currentUserId)
            ->whereNotNull('read_at')
            ->max('id');

        $newChronologies = TicketChronology::with(['user', 'replyTo.user'])
            ->where('tiket_id', $tiket->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($chron) use ($currentUserId) {
                $isSelf = $currentUserId && $chron->user_id === $currentUserId;
                return [
                    'id' => $chron->id,
                    'tiket_id' => $chron->tiket_id,
                    'user_id' => $chron->user_id,
                    'sender_name' => $isSelf ? 'Anda' : ($chron->user?->name ?? 'User'),
                    'is_self' => $isSelf,
                    'sender_badge' => $chron->user?->chat_badge ?? ($chron->user?->division ?: 'Petugas'),
                    'sender_avatar' => $chron->user?->avatar_url,
                    'sender_initials' => $chron->user?->initials ?? 'U',
                    'message' => $chron->message,
                    'category' => $chron->category ?? 'LAIN',
                    'time_str' => $chron->created_at ? $chron->created_at->format('H:i') . ' WIB' : '',
                    'date_str' => $chron->created_at ? $chron->created_at->format('d M Y, H:i') . ' WIB' : '',
                    'latitude' => $chron->latitude ? (float) $chron->latitude : null,
                    'longitude' => $chron->longitude ? (float) $chron->longitude : null,
                    'photo_url' => $chron->photo_url,
                    'video_url' => $chron->video_url,
                    'reply_to_id' => $chron->reply_to_id,
                    'reply_sender' => $chron->replyTo?->user?->name,
                    'reply_snippet' => $chron->replyTo ? ($chron->replyTo->message ?: ($chron->replyTo->latitude ? '📍 Titik Lokasi Lapangan' : '📷 Lampiran Foto')) : null,
                    'is_read' => !empty($chron->read_at),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $newChronologies,
            'max_read_self_id' => $maxReadSelfId,
        ]);
    }

    /**
     * Explicit mark read endpoint for real-time receipt acknowledgement.
     */
    public function markRead(Request $request, Tiket $tiket): JsonResponse
    {
        Gate::authorize('view', $tiket);

        $currentUserId = $request->user()?->id;
        if ($currentUserId) {
            TicketChronology::where('tiket_id', $tiket->id)
                ->where('user_id', '!=', $currentUserId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $maxReadSelfId = (int) TicketChronology::where('tiket_id', $tiket->id)
            ->where('user_id', $currentUserId)
            ->whereNotNull('read_at')
            ->max('id');

        return response()->json([
            'success' => true,
            'max_read_self_id' => $maxReadSelfId,
        ]);
    }

    /**
     * Stream chronology photo attachment reliably across Docker/VPS environments.
     */
    public function photo(TicketChronology $chronology): BinaryFileResponse
    {
        if (empty($chronology->photo_path)) {
            abort(404);
        }

        $cleanPath = ltrim($chronology->photo_path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return response()->file(Storage::disk('public')->path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return response()->file(public_path('storage/' . $cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path($cleanPath))) {
            return response()->file(public_path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }

    /**
     * Stream chronology video attachment reliably across Docker/VPS environments.
     */
    public function video(TicketChronology $chronology): BinaryFileResponse
    {
        if (empty($chronology->video_path)) {
            abort(404);
        }

        $cleanPath = ltrim($chronology->video_path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return response()->file(Storage::disk('public')->path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path('storage/' . $cleanPath))) {
            return response()->file(public_path('storage/' . $cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (file_exists(public_path($cleanPath))) {
            return response()->file(public_path($cleanPath), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }
}
