<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tiket;
use App\Modules\Ticketing\Services\ChronologyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            'category' => ['required', 'string', 'in:IZIN,OTDR,TRACING,MATERIAL,JOINTING,LINK_UP,SELESAI,LAIN'],
            'message' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'gps_accuracy' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
            'video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:20480'], // 20MB
        ]);

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
                category: $validated['category'],
                message: $validated['message'],
                photoPath: $photoPath,
                videoPath: $videoPath,
                latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                gpsAccuracy: isset($validated['gps_accuracy']) ? (float) $validated['gps_accuracy'] : null,
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
}
