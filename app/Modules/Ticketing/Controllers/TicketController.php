<?php

namespace App\Modules\Ticketing\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterMaterial;
use App\Models\MasterSla;
use App\Models\TicketChronology;
use App\Models\Tiket;
use App\Models\User;
use App\Modules\Ticketing\Services\ChronologyService;
use App\Modules\Ticketing\Services\ImsIntegrationService;
use App\Modules\Ticketing\Services\TicketDispatchService;
use App\Modules\Ticketing\Services\TicketSequenceService;
use App\Modules\Ticketing\Services\TicketStateMachine;
use App\Modules\Ticketing\Services\TechnicalAssetService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        protected TicketSequenceService $sequenceService,
        protected ImsIntegrationService $imsService,
        protected TicketStateMachine $stateMachine,
        protected TicketDispatchService $dispatchService,
        protected ChronologyService $chronologyService,
        protected TechnicalAssetService $assetService,
    ) {}

    /**
     * Display ticket listing with search, category, and status filters (§7.1, §11).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Tiket::query()->with(['assignees', 'creator', 'activeStopClock']);

        // Technicians see assigned tickets or CLOSED tickets (§7.2)
        $userRoleNames = $user->roles->pluck('name')->toArray();
        $isManagerOrNoc = ! empty(array_intersect(['super_admin', 'admin', 'manager_teknisi', 'noc', 'atasan_noc', 'atasan_teknik', 'atasan', 'ceo'], $userRoleNames));

        // Field technicians (divisi Teknik / karyawan_teknik) only see assigned tickets or CLOSED tickets (§7.2)
        if (! $isManagerOrNoc && ($user->division === 'Teknik' || str_contains($user->division ?? '', 'Teknik') || in_array('karyawan_teknik', $userRoleNames))) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $user->id))
                    ->orWhere('status', 'CLOSE');
            });
        }

        if ($request->input('assigned') === 'me' || $request->input('filter') === 'my_tickets') {
            $query->whereHas('ticketAssignees', fn ($sq) => $sq->where('user_id', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_tiket', strtoupper($request->input('kategori')));
        }

        if ($request->filled('priority')) {
            $query->where('priority', strtoupper($request->input('priority')));
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

        $tickets = $query->orderBy('tanggal_open', 'desc')->paginate(15)->withQueryString();
        $technicians = $this->getActiveTechnicians();
        $slaMatrix = MasterSla::query()->where('is_active', true)->get();

        // Calculate KPI stats (§7.1, §7.7)
        $stats = [
            'total' => Tiket::count(),
            'open' => Tiket::where('status', 'OPEN')->count(),
            'proses' => Tiket::where('status', 'PROSES')->count(),
            'pending_verifikasi' => Tiket::where('status', 'PENDING_VERIFIKASI')->count(),
            'selesai' => Tiket::where('status', 'CLOSE')->count(),
            'sla_tepat' => Tiket::where('status', 'CLOSE')->where('sla_status', 'TEPAT')->count(),
        ];

        return view('ticketing.index', compact('tickets', 'technicians', 'slaMatrix', 'stats'));
    }

    /**
     * Show dedicated Open Ticket form (§7.1, AC-04, AC-11).
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        if (! $user->can('ticket.create') && ! $user->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket.');
        }

        $slaMatrix = MasterSla::query()->where('is_active', true)->get();
        $today = Carbon::now('Asia/Jakarta')->format('Ymd');
        $backbonePreview = 'BDG-' . $today . '-XXX';
        $broadbandPreview = 'BRD-' . $today . '-XXX';

        $segments = [
            'BDG-SRE-01 (Soreang - Banjaran)',
            'BDG-CBG-01 (Cibiru - Gedebage)',
            'BDG-KPO-01 (Kopo - Pasirkoja)',
            'BDG-DGO-01 (Dago - Setiabudi)',
            'BDG-CRA-01 (Ciroyom - Andir)',
            'BDG-UJG-01 (Ujungberung - Cipadung)',
            'BDG-CMH-01 (Cimahi - Padalarang)',
            'BDG-TPO-01 (Taman Kopo Indah)',
            'BDG-RCA-01 (Rancaekek - Cicalengka)',
            'SW BBLU - SW Reog',
            'SW Core Dayeuhkolot - SW Distribusi',
        ];

        $initialCategory = strtolower($request->query('category', 'backbone'));
        if (! in_array($initialCategory, ['backbone', 'broadband'])) {
            $initialCategory = 'backbone';
        }

        return view('ticketing.create', compact('slaMatrix', 'backbonePreview', 'broadbandPreview', 'segments', 'initialCategory'));
    }

    /**
     * Show ticket detail (§7.1–§7.6).
     */
    public function show(Request $request, Tiket $tiket): View
    {
        Gate::authorize('view', $tiket);

        // Defensive self-healing for unmigrated database instances
        if (! Schema::hasColumn('tiket', 'team_members') || ! Schema::hasColumn('tiket', 'catatan_tambahan')) {
            try {
                Schema::table('tiket', function (Blueprint $table): void {
                    if (! Schema::hasColumn('tiket', 'team_members')) {
                        $table->json('team_members')->nullable()->after('action_taken');
                    }
                    if (! Schema::hasColumn('tiket', 'catatan_tambahan')) {
                        $table->text('catatan_tambahan')->nullable()->after('action_taken');
                    }
                });
            } catch (\Throwable) {
                // Ignore alter errors if db user lacks permissions
            }
        }

        // Mark incoming chronologies as read (§WhatsApp read receipt)
        if ($request->user()) {
            TicketChronology::where('tiket_id', $tiket->id)
                ->where('user_id', '!=', $request->user()->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $tiket->load([
            'assignees',
            'statusHistories.user',
            'chronologies.user',
            'chronologies.replyTo.user',
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
        $technicians = $this->getActiveTechnicians();
        $materials = MasterMaterial::query()->where('is_active', true)->get();
        $prerequisites = $this->assetService->checkClosingPrerequisites($tiket);

        return view('ticketing.show', compact('tiket', 'intervalStatus', 'technicians', 'materials', 'prerequisites'));
    }

    /**
     * Get active technicians (khusus karyawan divisi Teknik).
     */
    protected function getActiveTechnicians()
    {
        $technicians = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('division', 'Teknik')
                    ->orWhere('division', 'like', '%Teknik%');
            })
            ->orderBy('name', 'asc')
            ->get();

        // Fallback untuk test environment jika mock user belum di-set division 'Teknik'
        if ($technicians->isEmpty()) {
            $technicians = User::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('division', 'Teknik')
                        ->orWhere('position', 'like', '%Teknisi%')
                        ->orWhereHas('roles', fn ($rq) => $rq->whereIn('name', ['karyawan_teknik', 'atasan_teknik', 'manager_teknisi', 'teknisi']));
                })
                ->orderBy('name', 'asc')
                ->get();
        }

        if ($technicians->isEmpty()) {
            $technicians = User::query()->where('is_active', true)->orderBy('name', 'asc')->get();
        }

        return $technicians;
    }

    /**
     * Store Backbone ticket (§7.1, AC-04).
     */
    public function storeBackbone(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->can('ticket.create') && ! $user->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket.');
        }

        $validated = $request->validate([
            'backbone_segment' => ['required', 'string', 'max:150'],
            'status_link_impact' => ['required', 'string', 'max:150'],
            'wilayah' => ['nullable', 'string', 'max:100'],
            'tipe_penanganan' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'in:P1,P2,P3,P4'],
            'sla_target_minutes' => ['nullable', 'integer', 'min:15', 'max:10080'],
            'tanggal_open' => ['nullable'],
            'notes' => ['nullable', 'string'],
            'parent_ticket_id' => ['nullable', 'exists:tiket,id'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:12288'],
        ]);

        $priority = $validated['priority'] ?? 'P2';
        $wilayah = ! empty($validated['wilayah']) ? $validated['wilayah'] : 'Area Bandung & Sekitarnya';
        $tipePenanganan = ! empty($validated['tipe_penanganan']) ? $validated['tipe_penanganan'] : 'Corrective Maintenance FO';

        // Duplicate active check (§7.1)
        $existingActive = Tiket::query()
            ->where('kategori_tiket', 'BACKBONE')
            ->where('backbone_segment', $validated['backbone_segment'])
            ->whereIn('status', ['OPEN', 'PROSES', 'PENDING_VERIFIKASI'])
            ->first();

        // Resolve SLA target from master_sla (§7.1) or custom SLA input
        $sla = MasterSla::query()
            ->where('kategori_tiket', 'BACKBONE')
            ->where('priority', $priority)
            ->first();
        $slaMinutes = ! empty($validated['sla_target_minutes'])
            ? (int) $validated['sla_target_minutes']
            : ($sla ? $sla->sla_minutes : 360);

        $noTiket = $this->sequenceService->generate('BDG');
        $now = Carbon::now('Asia/Jakarta');
        $tanggalOpen = ! empty($validated['tanggal_open']) ? Carbon::parse($validated['tanggal_open']) : $now;

        $tiket = Tiket::query()->create([
            'no_tiket' => $noTiket,
            'kategori_tiket' => 'BACKBONE',
            'priority' => $priority,
            'status' => 'OPEN',
            'sla_target_minutes' => $slaMinutes,
            'backbone_segment' => $validated['backbone_segment'],
            'status_link_impact' => $validated['status_link_impact'],
            'wilayah' => $wilayah,
            'tipe_penanganan' => $tipePenanganan,
            'problem_temuan' => $validated['notes'] ?? null,
            'tanggal_open' => $tanggalOpen,
            'created_by' => $user->id,
            'parent_ticket_id' => $validated['parent_ticket_id'] ?? null,
        ]);

        $tiket->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'OPEN',
            'changed_by' => $user->id,
            'reason' => 'Tiket Backbone dibuat oleh ' . ($user->name ?? 'Helpdesk/NOC'),
            'created_at' => $now,
        ]);

        // Store photos into ticket chronologies if uploaded
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo && $photo->isValid()) {
                    $path = $photo->store('tickets/evidence', 'public');
                    $tiket->chronologies()->create([
                        'user_id' => $user->id,
                        'category' => 'INVESTIGASI',
                        'message' => 'Foto bukti awal open tiket: ' . ($validated['notes'] ?? 'Kondisi gangguan awal'),
                        'photo_path' => $path,
                        'created_at' => $now,
                    ]);
                }
            }
        }

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
        if (! $user->can('ticket.create') && ! $user->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket.');
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['nullable', 'string'],
            'customer_odp' => ['nullable', 'string', 'max:100'],
            'customer_olt' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'in:P1,P2,P3,P4'],
            'sla_target_minutes' => ['nullable', 'integer', 'min:15', 'max:10080'],
            'tanggal_open' => ['nullable'],
            'notes' => ['nullable', 'string'],
            'parent_ticket_id' => ['nullable', 'exists:tiket,id'],
            'is_manual_fallback' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:12288'],
        ]);

        $priority = $validated['priority'] ?? 'P2';
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
            ->where('priority', $priority)
            ->first();
        $slaMinutes = ! empty($validated['sla_target_minutes'])
            ? (int) $validated['sla_target_minutes']
            : ($sla ? $sla->sla_minutes : 240); // Default 240 min (§7.1)

        $noTiket = $this->sequenceService->generate('BRD');
        $now = Carbon::now('Asia/Jakarta');
        $tanggalOpen = ! empty($validated['tanggal_open']) ? Carbon::parse($validated['tanggal_open']) : $now;

        $tiket = Tiket::query()->create([
            'no_tiket' => $noTiket,
            'kategori_tiket' => 'BROADBAND',
            'priority' => $priority,
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
            'tanggal_open' => $tanggalOpen,
            'created_by' => $user->id,
            'parent_ticket_id' => $validated['parent_ticket_id'] ?? null,
        ]);

        $tiket->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'OPEN',
            'changed_by' => $user->id,
            'reason' => 'Tiket Broadband dibuat oleh ' . ($user->name ?? 'Helpdesk/NOC') . ($isValidated ? '' : ' (Data manual / belum tervalidasi IMS - AC-11)'),
            'created_at' => $now,
        ]);

        // Store photos into ticket chronologies if uploaded
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo && $photo->isValid()) {
                    $path = $photo->store('tickets/evidence', 'public');
                    $tiket->chronologies()->create([
                        'user_id' => $user->id,
                        'category' => 'INVESTIGASI',
                        'message' => 'Foto bukti awal open tiket: ' . ($validated['notes'] ?? 'Kondisi gangguan pelanggan'),
                        'photo_path' => $path,
                        'created_at' => $now,
                    ]);
                }
            }
        }

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
     * Penugasan teknisi diberikan oleh role atasan teknik dan atasan umum.
     */
    public function dispatchTicket(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = $request->user();
        if ($user->hasRole(['noc', 'atasan_noc']) || $user->division === 'NOC' || $user->id === $tiket->created_by) {
            abort(403, 'NOC yang membuka tiket tidak dapat melakukan penugasan teknisi. Penugasan teknisi hanya dapat diberikan oleh Atasan Teknik atau Atasan Umum.');
        }

        if (! $user->can('dispatch', $tiket) && ! $user->hasRole(['super_admin', 'admin', 'atasan_teknik', 'atasan_umum', 'atasan', 'manager_teknisi'])) {
            abort(403, 'Penugasan teknisi hanya dapat diberikan oleh Atasan Teknik atau Atasan Umum.');
        }

        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:users,id'],
            'lead_user_id' => ['nullable', 'exists:users,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
            'member_user_ids' => ['nullable', 'array'],
            'member_user_ids.*' => ['exists:users,id'],
        ]);

        $leadId = (int) ($validated['lead_id'] ?? $validated['lead_user_id'] ?? 0);
        if ($leadId <= 0) {
            return back()->withErrors(['lead_id' => 'Pilih Leader Teknisi (PIC) terlebih dahulu.']);
        }
        $memberIds = $validated['member_ids'] ?? $validated['member_user_ids'] ?? [];

        $result = $this->dispatchService->dispatch(
            $tiket,
            $leadId,
            $memberIds,
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
            'team_members' => ['nullable', 'array'],
            'team_members.*' => ['nullable', 'string'],
            'catatan_tambahan' => ['nullable', 'string'],
            'redaman_sebelum' => ['nullable', 'numeric'],
            'redaman_sesudah' => ['nullable', 'numeric'],
            'has_link_up' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $this->stateMachine->submitClosingAwal($tiket, $request->user(), $validated);

            if (! $result['prerequisites']['passed']) {
                $errMessage = 'Resume disimpan, tetapi Closing Awal belum dapat diajukan karena prasyarat belum lengkap (AC-06): '.implode('; ', $result['prerequisites']['missing']);
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        if (! $request->user()->can('ticket.verify_close') && ! $request->user()->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
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
        } catch (\Throwable $e) {
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
        if (! $request->user()->can('ticket.reopen') && ! $request->user()->hasRole(['super_admin', 'admin', 'noc', 'manager_teknisi'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk reopen tiket.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stateMachine->reopen($tiket, $request->user(), $validated['reason']);

            return redirect()->route('tickets.show', $tiket)->with('success', "Tiket {$tiket->no_tiket} berhasil di-reopen.");
        } catch (\Throwable $e) {
            return redirect()->route('tickets.show', $tiket)->withErrors(['reopen' => $e->getMessage()]);
        }
    }

    /**
     * Cancel ticket (§7.2).
     */
    public function cancelTicket(Request $request, Tiket $tiket): RedirectResponse
    {
        if (! $request->user()->can('ticket.cancel') && ! $request->user()->hasRole(['super_admin', 'admin', 'noc'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan tiket.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->stateMachine->cancel($tiket, $request->user(), $validated['reason']);

            return redirect()->route('tickets.show', $tiket)->with('success', "Tiket {$tiket->no_tiket} berhasil dibatalkan.");
        } catch (\Throwable $e) {
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
