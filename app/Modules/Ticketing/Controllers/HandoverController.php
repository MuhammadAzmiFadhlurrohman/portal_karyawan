<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TicketHandover;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Ticketing\Services\HandoverService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HandoverController extends Controller
{
    public function __construct(
        protected HandoverService $handoverService,
    ) {}

    /**
     * Create shift handover (§7.5, AC-05).
     */
    public function store(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $tiket);

        $validated = $request->validate([
            'shift_from' => ['required', 'string', 'max:50'],
            'shift_to' => ['required', 'string', 'max:50'],
            'recipient_id' => ['required', 'exists:users,id'],
            'notes' => ['required', 'string'],
        ]);

        try {
            $recipient = User::query()->findOrFail((int) $validated['recipient_id']);
            $handover = $this->handoverService->createHandover(
                tiket: $tiket,
                shiftFrom: $validated['shift_from'],
                shiftTo: $validated['shift_to'],
                sender: $request->user(),
                recipient: $recipient,
                notes: $validated['notes'],
            );

            $msg = "Handover shift kepada {$recipient->name} berhasil dibuat.";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'handover' => $handover]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['handover' => $e->getMessage()]);
        }
    }

    /**
     * Acknowledge shift handover (§7.5).
     */
    public function acknowledge(Request $request, TicketHandover $handover): RedirectResponse|JsonResponse
    {
        try {
            $this->handoverService->acknowledge($handover, $request->user());

            $msg = 'Handover shift telah berhasil dikonfirmasi.';
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('tickets.show', $handover->ticket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $handover->ticket)->withErrors(['handover' => $e->getMessage()]);
        }
    }
}
