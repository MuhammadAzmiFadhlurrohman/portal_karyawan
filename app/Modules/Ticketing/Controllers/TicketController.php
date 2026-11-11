<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterMaterial;
use App\Models\MasterSla;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Ticketing\Services\ChronologyService;
use App\Modules\Ticketing\Services\ImsIntegrationService;
use App\Modules\Ticketing\Services\TicketDispatchService;
use App\Modules\Ticketing\Services\TicketSequenceService;
use App\Modules\Ticketing\Services\TicketStateMachine;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        protected TicketSequenceService $sequenceService,
        protected ImsIntegrationService $imsService,
        protected TicketStateMachine $stateMachine,
        protected TicketDispatchService $dispatchService,
        protected ChronologyService $chronologyService,
    ) {}

    /**
     * Display ticket listing with search, category, and status filters (§7.1, §11).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Tiket::query()->with(['assignees', 'creator', 'activeStopClock']);

        // Technicians see assigned tickets or CLOSED tickets (§7.2)
        if ($user->hasRole('teknisi') && ! $user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk', 'sa_cs', 'ceo', 'noc'])) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $user->id))
                    ->orWhere('status', 'CLOSE');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_tiket', strtoupper($request->input('kategori')));
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('no_tiket', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_id', 'like', "%{$term}%")
                    ->orWhere('backbone_segment', 'like', "%{$term}%");
            });
        }

        $tickets = $query->orderBy('tanggal_open', 'desc')->paginate(15);
        $technicians = User::role('teknisi')->where('is_active', true)->get();
        $slaMatrix = MasterSla::query()->where('is_active', true)->get();

        return view('ticketing.index', compact('tickets', 'technicians', 'slaMatrix'));
    }

    /**
     * Show ticket detail (§7.1–§7.6).
     */
    public function show(Request $request, Tiket $tiket): View
    {
        Gate::authorize('view', $tiket);

        $tiket->load([
            'assignees',
            'statusHistories.user',
            'chronologies.user',
            'breaches',
            'stopClocks.starter',
            'stopClocks.approver',
            'handovers.sender',
            'handovers.recipient',
            'materials.material',
            'repairPoints',
            'jointClosures.cores',
            'coreManeuvers',
            'creator',
            'closingAwalUser',
            'verifierUser',
        ]);

        $intervalStatus = $this->chronologyService->getIntervalStatus($tiket);
        $technicians = User::role('teknisi')->where('is_active', true)->get();
        $materials = MasterMaterial::query()->where('is_active', true)->get();

        return view('ticketing.show', compact('tiket', 'intervalStatus', 'technicians', 'materials'));
    }

    /**
     * Store Backbone ticket (§7.1, AC-04).
     */
    public function storeBackbone(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.create') && ! $user->hasRole(['super_admin', 'helpdesk', 'noc', 'sa_cs', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket.');
        }

        $validated = $request->validate([
            'backbone_segment' => ['required', 'string', 'max:150'],
            'status_link_impact' => ['required', 'string', 'in:TOTAL_DOWN,DEGRADED,NO_IMPACT'],
            'wilayah' => ['required', 'string', 'max:100'],
            'tipe_penanganan' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'in:P1,P2,P3,P4'],
            'notes' => ['nullable', 'string'],
            'parent_ticket_id' => ['nullable', 'exists:tiket,id'],
        ]);

        // Duplicate active check (§7.1)
        $existingActive = Tiket::query()
            ->where('kategori_tiket', 'BACKBONE')
            ->where('backbone_segment', $validated['backbone_segment'])
            ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
            ->first();

        // Resolve SLA target from master_sla (§7.1)
        $sla = MasterSla::query()
            ->where('kategori_tiket', 'BACKBONE')
            ->where('priority', $validated['priority'])
            ->first();
        $slaMinutes = $sla ? $sla->sla_minutes : 360; // Default 360 min

        $noTiket = $this->sequenceService->generate('BDG');
        $now = Carbon::now('Asia/Jakarta');

        $tiket = Tiket::query()->create([
            'no_tiket' => $noTiket,
            'kategori_tiket' => 'BACKBONE',
            'priority' => $validated['priority'],
            'status' => 'OPEN',
            'sla_target_minutes' => $slaMinutes,
            'backbone_segment' => $validated['backbone_segment'],
            'status_link_impact' => $validated['status_link_impact'],
            'wilayah' => $validated['wilayah'],
            'tipe_penanganan' => $validated['tipe_penanganan'],
            'problem_temuan' => $validated['notes'] ?? null,
            'tanggal_open' => $now,
            'created_by' => $user->id,
            'parent_ticket_id' => $validated['parent_ticket_id'] ?? null,
        ]);

        $tiket->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'OPEN',
            'changed_by' => $user->id,
            'reason' => 'Tiket Backbone dibuat oleh Helpdesk',
            'created_at' => $now,
        ]);

        $msg = "Tiket Backbone {$noTiket} berhasil dibuat.";
        if ($existingActive) {
            $msg .= " (Peringatan: Terdapat tiket aktif {$existingActive->no_tiket} pada segmen yang sama)";
        }

        return redirect()->route('tickets.show', $tiket)->with('success', $msg);
    }

    /**
     * Store Broadband ticket with IMS customer snapshot or fallback manual (AC-04, AC-11, §7.1).
     */
    public function storeBroadband(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.create') && ! $user->hasRole(['super_admin', 'helpdesk', 'noc', 'sa_cs', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket.');
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['nullable', 'string'],
            'customer_odp' => ['nullable', 'string', 'max:100'],
            'customer_olt' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'string', 'in:P1,P2,P3,P4'],
            'notes' => ['nullable', 'string'],
            'parent_ticket_id' => ['nullable', 'exists:tiket,id'],
            'is_manual_fallback' => ['nullable', 'boolean'],
        ]);

        $cid = $validated['customer_id'] ?? null;
        $snapshot = null;
        $isValidated = true;

        if ($cid) {
            $imsResult = $this->imsService->getCustomerByCid($cid);
            if ($imsResult['available'] && $imsResult['customer']) {
                $snapshot = $imsResult['customer'];
                $isValidated = true;
            } else {
                // IMS service unavailable or customer not found -> Fallback (AC-11)
                $isValidated = false;
            }
        } else {
            // Manual entry without IMS CID
            $isValidated = false;
        }

        // Duplicate active check (§7.1)
        $existingActive = null;
        if ($cid) {
            $existingActive = Tiket::query()
                ->where('kategori_tiket', 'BROADBAND')
                ->where('customer_id', $cid)
                ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
                ->first();
        }

        $sla = MasterSla::query()
            ->where('kategori_tiket', 'BROADBAND')
            ->where('priority', $validated['priority'])
            ->first();
        $slaMinutes = $sla ? $sla->sla_minutes : 240; // Default 240 min (§7.1)

        $noTiket = $this->sequenceService->generate('BRD');
        $now = Carbon::now('Asia/Jakarta');

        $tiket = Tiket::query()->create([
            'no_tiket' => $noTiket,
            'kategori_tiket' => 'BROADBAND',
            'priority' => $validated['priority'],
            'status' => 'OPEN',
            'sla_target_minutes' => $slaMinutes,
            'customer_id' => $cid,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_odp' => $validated['customer_odp'] ?? null,
            'customer_olt' => $validated['customer_olt'] ?? null,
            'customer_snapshot' => $snapshot,
            'is_ims_validated' => $isValidated,
            'problem_temuan' => $validated['notes'] ?? null,
            'tanggal_open' => $now,
            'created_by' => $user->id,
            'parent_ticket_id' => $validated['parent_ticket_id'] ?? null,
        ]);

        $tiket->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'OPEN',
            'changed_by' => $user->id,
            'reason' => 'Tiket Broadband dibuat oleh Helpdesk'.($isValidated ? '' : ' (Data manual / belum tervalidasi IMS - AC-11)'),
            'created_at' => $now,
        ]);

        $msg = "Tiket Broadband {$noTiket} berhasil dibuat.";
        if (! $isValidated) {
            $msg .= ' (Perhatian: Tiket berstatus Belum Tervalidasi IMS - AC-11)';
        }
        if ($existingActive) {
            $msg .= " (Peringatan: Terdapat tiket aktif {$existingActive->no_tiket} untuk pelanggan ini)";
        }

        return redirect()->route('tickets.show', $tiket)->with('success', $msg);
    }

    /**
     * Dispatch ticket (§7.2).
     */
    public function dispatchTicket(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.dispatch') && ! $user->hasRole(['super_admin', 'manager_teknisi', 'helpdesk'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk dispatch tiket.');
        }

        $validated = $request->validate([
            'lead_id' => ['required', 'exists:users,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        $result = $this->dispatchService->dispatch(
            $tiket,
            (int) $validated['lead_id'],
            $validated['member_ids'] ?? [],
            $user
        );

        $msg = "Tiket {$tiket->no_tiket} berhasil di-dispatch.";
        if (! empty($result['overload_warnings'])) {
            $msg .= ' Catatan beban: '.implode(' | ', $result['overload_warnings']);
        }

        return redirect()->route('tickets.show', $tiket)->with('success', $msg);
    }

    /**
     * Submit Closing Awal by assigned technician (AC-05, AC-06, AC-07, §7.2, §7.6).
     */
    public function closingAwal(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('closingAwal', $tiket);

        $validated = $request->validate([
            'problem_temuan' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'redaman_sebelum' => ['nullable', 'numeric'],
            'redaman_sesudah' => ['nullable', 'numeric'],
            'has_link_up' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $this->stateMachine->submitClosingAwal($tiket, $request->user(), $validated);

            if (! $result['prerequisites']['passed']) {
                $errMessage = 'Closing Awal ditolak karena prasyarat belum terpenuhi (AC-06): '.implode('; ', $result['prerequisites']['missing']);
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errMessage,
                        'missing' => $result['prerequisites']['missing'],
                    ], 422);
                }

                return redirect()->route('tickets.show', $tiket)->withErrors(['closing' => $errMessage]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Closing Awal tiket {$tiket->no_tiket} berhasil diajukan untuk verifikasi.",
                    'tiket' => $result['tiket'],
                ]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', "Closing Awal tiket {$tiket->no_tiket} berhasil diajukan untuk verifikasi.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['closing' => $e->getMessage()]);
        }
    }

    /**
     * Final verify and close ticket by Helpdesk (AC-08, §7.2, §7.7).
     */
    public function verifyClose(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        Gate::authorize('verifyClose', $tiket);

        try {
            $closedTiket = $this->stateMachine->verifyAndClose($tiket, $request->user());

            $msg = "Tiket {$tiket->no_tiket} berhasil diverifikasi & DITUTUP. MTTR Bersih: {$closedTiket->net_mttr_minutes}m, Status SLA: {$closedTiket->sla_status}.";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'tiket' => $closedTiket]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['verify' => $e->getMessage()]);
        }
    }

    /**
     * Reject Closing Awal (§7.2).
     */
    public function rejectClosing(Request $request, Tiket $tiket): RedirectResponse|JsonResponse
    {
        if (! $request->user()->can('ticket.verify_close') && ! $request->user()->hasRole(['super_admin', 'helpdesk', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak closing verifikasi.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stateMachine->rejectClosing($tiket, $request->user(), $validated['reason']);

            $msg = "Closing tiket {$tiket->no_tiket} telah ditolak dan dikembalikan ke teknisi.";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('tickets.show', $tiket)->with('success', $msg);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('tickets.show', $tiket)->withErrors(['reject' => $e->getMessage()]);
        }
    }

    /**
     * Reopen closed ticket (§7.2).
     */
    public function reopenTicket(Request $request, Tiket $tiket): RedirectResponse
    {
        if (! $request->user()->can('ticket.reopen') && ! $request->user()->hasRole(['super_admin', 'helpdesk', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk reopen tiket.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stateMachine->reopen($tiket, $request->user(), $validated['reason']);

            return redirect()->route('tickets.show', $tiket)->with('success', "Tiket {$tiket->no_tiket} berhasil di-reopen.");
        } catch (Exception $e) {
            return redirect()->route('tickets.show', $tiket)->withErrors(['reopen' => $e->getMessage()]);
        }
    }

    /**
     * Cancel ticket (§7.2).
     */
    public function cancelTicket(Request $request, Tiket $tiket): RedirectResponse
    {
        if (! $request->user()->can('ticket.cancel') && ! $request->user()->hasRole(['super_admin', 'helpdesk', 'noc'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan tiket.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stateMachine->cancel($tiket, $request->user(), $validated['reason']);

            return redirect()->route('tickets.show', $tiket)->with('success', "Tiket {$tiket->no_tiket} berhasil dibatalkan.");
        } catch (Exception $e) {
            return redirect()->route('tickets.show', $tiket)->withErrors(['cancel' => $e->getMessage()]);
        }
    }

    /**
     * Search IMS customers API proxy (§7.1, AC-11).
     */
    public function searchIms(Request $request): JsonResponse
    {
        $q = (string) $request->input('q', '');
        $res = $this->imsService->searchCustomers($q);

        return response()->json($res);
    }
}
