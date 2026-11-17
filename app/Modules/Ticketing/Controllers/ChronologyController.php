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
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kronologis berhasil ditambahkan.',
                    'chronology' => $entry,
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
