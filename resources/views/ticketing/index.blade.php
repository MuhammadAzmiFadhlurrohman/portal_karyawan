@extends('layouts.app', ['title' => 'Tiketing Gangguan NOC & Lapangan'])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                Modul Tiketing Gangguan
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                Pengelolaan tiket gangguan Backbone (<code class="bg-blue-100 px-1 py-0.5 rounded text-blue-800">BDG-</code>) dan Broadband (<code class="bg-indigo-100 px-1 py-0.5 rounded text-indigo-800">BRD-</code>), SLA MTTR, dan aset teknis lapangan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('ticket.create')
            <button type="button" onclick="document.getElementById('modal-backbone').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E40AF] hover:bg-[#1D4ED8] text-white text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                + Tiket Backbone
            </button>

            <button type="button" onclick="document.getElementById('modal-broadband').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0D9488] hover:bg-[#0F766E] text-white text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                + Tiket Broadband
            </button>
            @endcan
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-blue-100 p-4 shadow-sm">
        <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Tiket / Pelanggan / Segmen</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="No tiket, nama, CID, segmen..."
                       class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                <select name="kategori" class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                    <option value="">Semua Kategori</option>
                    <option value="BACKBONE" {{ request('kategori') === 'BACKBONE' ? 'selected' : '' }}>Backbone (BDG-)</option>
                    <option value="BROADBAND" {{ request('kategori') === 'BROADBAND' ? 'selected' : '' }}>Broadband (BRD-)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full text-sm rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
                    <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES</option>
                    <option value="PENDING_VERIFIKASI" {{ request('status') === 'PENDING_VERIFIKASI' ? 'selected' : '' }}>PENDING VERIFIKASI</option>
                    <option value="CLOSE" {{ request('status') === 'CLOSE' ? 'selected' : '' }}>CLOSE</option>
                    <option value="REOPENED" {{ request('status') === 'REOPENED' ? 'selected' : '' }}>REOPENED</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl shadow-sm transition">
                    Filter
                </button>
                <a href="{{ route('tickets.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Ticket Cards Grid with Color Stripes (§11, AC-08) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($tickets as $ticket)
            @php
                $stripe = $ticket->stripeColor();
                $stripeBg = match($stripe) {
                    'red' => 'bg-red-500',
                    'yellow' => 'bg-amber-400',
                    'green' => 'bg-emerald-500',
                    'gray' => 'bg-slate-400',
                    default => 'bg-blue-600',
                };
                $lead = $ticket->assignees->firstWhere('pivot.role_in_team', 'lead');
            @endphp
            <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between">
                <!-- Stripe Bar on Left Border (§11) -->
                <div class="absolute top-0 bottom-0 left-0 w-2 {{ $stripeBg }}"></div>

                <div class="p-4 pl-6 space-y-3">
                    <!-- Top Line: Ticket No & Priority Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('tickets.show', $ticket) }}" class="font-heading font-bold text-base text-[#1E3A8A] hover:underline">
                            {{ $ticket->no_tiket }}
                        </a>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ticket->priority === 'P1' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'P2' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $ticket->priority }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $ticket->status === 'CLOSE' ? 'bg-green-100 text-green-800' : ($ticket->status === 'CANCELLED' ? 'bg-slate-100 text-slate-700' : ($ticket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-100 text-purple-800' : ($ticket->status === 'PROSES' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'))) }}">
                                {{ $ticket->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="text-xs text-slate-600 space-y-1">
                        @if($ticket->kategori_tiket === 'BACKBONE')
                            <p><strong class="text-slate-800">Segmen:</strong> {{ $ticket->backbone_segment ?? '-' }}</p>
                            <p><strong class="text-slate-800">Dampak:</strong> <span class="font-medium text-red-600">{{ $ticket->status_link_impact ?? '-' }}</span> ({{ $ticket->wilayah }})</p>
                        @else
                            <p><strong class="text-slate-800">Pelanggan:</strong> {{ $ticket->customer_name }} @if(!$ticket->is_ims_validated) <span class="text-amber-600 font-bold">(Manual / Non-IMS)</span> @endif</p>
                            <p><strong class="text-slate-800">CID:</strong> {{ $ticket->customer_id ?? 'N/A' }} | <strong>ODP:</strong> {{ $ticket->customer_odp ?? '-' }}</p>
                        @endif
                        <p><strong class="text-slate-800">Buka:</strong> {{ $ticket->tanggal_open ? $ticket->tanggal_open->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB</p>
                    </div>

                    <!-- SLA & Team Info -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <div>
                            <span>Target SLA: <strong>{{ $ticket->sla_target_minutes }}m</strong></span>
                            @if($ticket->activeStopClock)
                                <span class="block text-amber-600 font-semibold">⏸ Stop Clock Aktif</span>
                            @elseif($ticket->isOverdue())
                                <span class="block text-red-600 font-bold">⚠️ OVERDUE SLA</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span>Lead: <strong>{{ $lead ? $lead->name : 'Belum di-dispatch' }}</strong></span>
                            <span class="block text-slate-400">{{ $ticket->assignees->count() }} teknisi</span>
                        </div>
                    </div>
                </div>

                <!-- Footer action link -->
                <div class="bg-slate-50 px-4 py-2 pl-6 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-500">{{ $ticket->kategori_tiket }}</span>
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                        Detail Tiket &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-blue-100 p-12 text-center text-slate-500">
                <svg class="mx-auto w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="font-medium text-base text-slate-700">Tidak ada tiket gangguan yang cocok dengan kriteria filter.</p>
                <p class="text-xs text-slate-400 mt-1">Gunakan tombol di atas untuk membuka tiket baru.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>

<!-- Modal Buka Tiket Backbone -->
<div id="modal-backbone" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-heading font-bold text-lg text-slate-900">Buka Tiket Gangguan Backbone (BDG-)</h3>
            <button type="button" onclick="document.getElementById('modal-backbone').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('tickets.backbone.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Segmen Backbone *</label>
                <input type="text" name="backbone_segment" required placeholder="Mis. BB-BDG-SRE-01" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Link Impact *</label>
                    <select name="status_link_impact" required class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                        <option value="TOTAL_DOWN">TOTAL DOWN</option>
                        <option value="DEGRADED">DEGRADED</option>
                        <option value="NO_IMPACT">NO IMPACT</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas *</label>
                    <select name="priority" required class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                        <option value="P1">P1 - Kritis (SLA 180m)</option>
                        <option value="P2" selected>P2 - Mayor (SLA 360m)</option>
                        <option value="P3">P3 - Minor (SLA 480m)</option>
                        <option value="P4">P4 - Terencana (SLA 720m)</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Wilayah *</label>
                    <input type="text" name="wilayah" required placeholder="Mis. Soreang" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Penanganan *</label>
                    <input type="text" name="tipe_penanganan" required placeholder="Mis. Splicing / Cut FO" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian Masalah Awal</label>
                <textarea name="notes" rows="2" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2" placeholder="Catatan indikasi awal..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-backbone').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm">Simpan & Terbitkan Tiket</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buka Tiket Broadband -->
<div id="modal-broadband" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-heading font-bold text-lg text-slate-900">Buka Tiket Gangguan Broadband (BRD-)</h3>
            <button type="button" onclick="document.getElementById('modal-broadband').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('tickets.broadband.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Customer ID (CID) / No Pelanggan</label>
                <div class="flex gap-2">
                    <input type="text" id="input_cid" name="customer_id" placeholder="Mis. CUST-10023" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                    <button type="button" onclick="lookupIms()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-xl">Cari IMS</button>
                </div>
                <p id="ims_feedback" class="text-xs text-slate-500 mt-1"></p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pelanggan *</label>
                    <input type="text" id="input_cust_name" name="customer_name" required placeholder="Nama lengkap" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / Telp *</label>
                    <input type="text" id="input_cust_phone" name="customer_phone" required placeholder="08..." class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">ODP</label>
                    <input type="text" id="input_cust_odp" name="customer_odp" placeholder="ODP-SRE-01" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas *</label>
                    <select name="priority" required class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
                        <option value="P1">P1 - VIP Down (SLA 120m)</option>
                        <option value="P2" selected>P2 - Standar Down (SLA 240m)</option>
                        <option value="P3">P3 - Intermittent (SLA 360m)</option>
                        <option value="P4">P4 - Minor Request (SLA 480m)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Pelanggan</label>
                <textarea id="input_cust_address" name="customer_address" rows="2" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2" placeholder="Alamat instalasi..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keluhan Pelanggan</label>
                <textarea name="notes" rows="2" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2" placeholder="Deskripsi kendala..."></textarea>
            </div>

            <!-- Fallback manual indicator (AC-11) -->
            <div class="bg-amber-50 p-2.5 rounded-xl border border-amber-200 text-xs text-amber-800">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_manual_fallback" value="1" class="rounded text-amber-600">
                    <span>Input Manual Darurat (Bila sistem IMS gangguan/tidak terjangkau - AC-11)</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-broadband').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm rounded-xl shadow-sm">Simpan & Terbitkan Tiket</button>
            </div>
        </form>
    </div>
</div>

<script>
function lookupIms() {
    const cid = document.getElementById('input_cid').value.trim();
    const feedback = document.getElementById('ims_feedback');
    if (!cid) {
        feedback.innerText = 'Masukkan CID terlebih dahulu.';
        return;
    }
    feedback.innerText = 'Mencari data IMS...';
    fetch(`{{ route('tickets.ims.search') }}?q=${encodeURIComponent(cid)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.available) {
                feedback.innerText = 'Layanan IMS sedang offline. Gunakan input manual (AC-11).';
                return;
            }
            if (data.customers && data.customers.length > 0) {
                const c = data.customers[0];
                document.getElementById('input_cust_name').value = c.name || '';
                document.getElementById('input_cust_phone').value = c.phone || '';
                document.getElementById('input_cust_address').value = c.address || '';
                document.getElementById('input_cust_odp').value = c.odp || '';
                feedback.innerText = `Data IMS ditemukan untuk ${c.name}.`;
            } else {
                feedback.innerText = 'Data tidak ditemukan di IMS. Silakan input manual.';
            }
        })
        .catch(() => {
            feedback.innerText = 'Gagal terhubung ke IMS. Silakan gunakan opsi input manual.';
        });
}
</script>
@endsection

