<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StopClock;
use App\Models\Tiket;
use App\Modules\Ticketing\Services\StopClockService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StopClockController extends Controller
{
    public function __construct(
        protected StopClockService $stopClockService,
    ) {}

    /**
     * Start Stop Clock on a ticket (§7.4, AC-05).
     */
    public function store(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        // AC-05: Non-assigned technician write attempt receives 403!
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'in:IZIN_AKSES,PIHAK_KETIGA_PLN,CUACA_EKSTRIM,MENUNGGU_MATERIAL,PERMINTAAN_PELANGGAN,FORCE_MAJEURE'],
            'reason_detail' => ['required', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('tickets/stopclock/evidence', 'public');
        }

        try {
            $stopClock = $this->stopClockService->start(
                tiket: $tiket,
                reason: $validated['reason'],
                reasonDetail: $validated['reason_detail'],
                evidencePath: $evidencePath,
                user: $request->user(),
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stop Clock berhasil diaktifkan.',
                    'stop_clock' => $stopClock,
                ]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', 'Stop Clock berhasil diaktifkan.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['stopclock' => $e->getMessage()]);
        }
    }

    /**
     * End Stop Clock (§7.4).
     */
    public function end(Request $request, StopClock $stopClock): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $stopClock->ticket);

        try {
            $this->stopClockService->end($stopClock, $request->user());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Stop Clock telah dihentikan.']);
            }

            return redirect()->route('tickets.show', $stopClock->ticket)->with('success', 'Stop Clock telah dihentikan.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $stopClock->ticket)->withErrors(['stopclock' => $e->getMessage()]);
        }
    }

    /**
     * Approve Stop Clock (§7.4, AC-07).
     */
    public function approve(Request $request, StopClock $stopClock): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.stop_clock.approve') && ! $user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui Stop Clock.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->stopClockService->approve($stopClock, $user, $validated['notes'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Stop Clock berhasil disetujui.']);
        }

        return redirect()->route('tickets.show', $stopClock->ticket)->with('success', 'Stop Clock berhasil disetujui.');
    }

    /**
     * Reject Stop Clock (§7.4).
     */
    public function reject(Request $request, StopClock $stopClock): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.stop_clock.approve') && ! $user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak Stop Clock.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->stopClockService->reject($stopClock, $user, $validated['reason']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Stop Clock telah ditolak.']);
        }

        return redirect()->route('tickets.show', $stopClock->ticket)->with('success', 'Stop Clock telah ditolak.');
    }
}
