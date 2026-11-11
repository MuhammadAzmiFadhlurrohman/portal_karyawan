@extends('layouts.app', ['title' => 'Tiket ' . $tiket->no_tiket])

@section('content')
<div class="space-y-6">
    @php
        $stripe = $tiket->stripeColor();
        $stripeBg = match($stripe) {
            'red' => 'bg-red-500',
            'yellow' => 'bg-amber-400',
            'green' => 'bg-emerald-500',
            'gray' => 'bg-slate-400',
            default => 'bg-blue-600',
        };
        $lead = $tiket->assignees->firstWhere('pivot.role_in_team', 'lead');
    @endphp

    <!-- Top Breadcrumb & Status Banner with Stripe (§11, AC-08) -->
    <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 pl-8">
        <div class="absolute top-0 bottom-0 left-0 w-3 {{ $stripeBg }}"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('tickets.index') }}" class="hover:underline">Tiketing</a>
                    <span>&rsaquo;</span>
                    <span>{{ $tiket->kategori_tiket }}</span>
                    <span>&rsaquo;</span>
                    <span class="text-slate-800">{{ $tiket->no_tiket }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-heading font-bold text-[#1E3A8A]">
                        {{ $tiket->no_tiket }}
                    </h1>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $tiket->priority === 'P1' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-800' }}">
                        Prioritas {{ $tiket->priority }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold
                        {{ $tiket->status === 'CLOSE' ? 'bg-green-100 text-green-800' : ($tiket->status === 'CANCELLED' ? 'bg-slate-100 text-slate-700' : ($tiket->status === 'PENDING_VERIFIKASI' ? 'bg-purple-100 text-purple-800' : ($tiket->status === 'PROSES' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'))) }}">
                        Status: {{ $tiket->status }}
                    </span>
                    @if($tiket->sla_status)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $tiket->sla_status === 'TEPAT' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            SLA: {{ $tiket->sla_status }} ({{ $tiket->net_mttr_minutes }}m)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Interval & SLA Status Badges (§7.3, §7.7) -->
            <div class="flex flex-wrap items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="text-xs">
                    <span class="text-slate-500 block">Kepatuhan 30m:</span>
                    <span class="font-bold inline-flex items-center gap-1
                        {{ $intervalStatus === 'NORMAL' ? 'text-emerald-600' : ($intervalStatus === 'WARNING' ? 'text-amber-600' : ($intervalStatus === 'STOP_CLOCK' ? 'text-blue-600' : 'text-red-600')) }}">
                        @if($intervalStatus === 'NORMAL') 🟢 NORMAL (≤20m)
                        @elseif($intervalStatus === 'WARNING') 🟡 WARNING (21-30m)
                        @elseif($intervalStatus === 'STOP_CLOCK') ⏸ STOP CLOCK
                        @else 🔴 OVERDUE (>30m)
                        @endif
                    </span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="text-xs">
                    <span class="text-slate-500 block">Target SLA:</span>
                    <span class="font-bold text-slate-800">{{ $tiket->sla_target_minutes }} Menit</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="text-xs">
                    <span class="text-slate-500 block">Jeda Stop Clock:</span>
                    <span class="font-bold text-amber-700">{{ $tiket->total_stop_clock_minutes }} Menit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm space-y-1">
            <strong>Terjadi Kesalahan:</strong>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabbed Navigation -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-4 overflow-x-auto text-sm font-semibold" aria-label="Tabs">
            <button type="button" onclick="switchTab('tab-summary')" id="btn-tab-summary" class="tab-btn pb-3 px-2 border-b-2 border-blue-600 text-blue-600">
                1. Ringkasan &amp; Tim
            </button>
            <button type="button" onclick="switchTab('tab-chronology')" id="btn-tab-chronology" class="tab-btn pb-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                2. Kronologis ({{ $tiket->chronologies->count() }})
            </button>
            <button type="button" onclick="switchTab('tab-stopclock')" id="btn-tab-stopclock" class="tab-btn pb-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                3. Stop Clock ({{ $tiket->stopClocks->count() }})
            </button>
            <button type="button" onclick="switchTab('tab-assets')" id="btn-tab-assets" class="tab-btn pb-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                4. Aset Teknis &amp; Material
            </button>
            <button type="button" onclick="switchTab('tab-handover')" id="btn-tab-handover" class="tab-btn pb-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                5. Handover Shift ({{ $tiket->handovers->count() }})
            </button>
            <button type="button" onclick="switchTab('tab-actions')" id="btn-tab-actions" class="tab-btn pb-3 px-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                6. Alur &amp; Closing
            </button>
        </nav>
    </div>

    <!-- TAB 1: RINGKASAN & TIM -->
    <div id="tab-summary" class="tab-pane space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Data Teknis Gangguan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Informasi Masalah</h3>
                <div class="text-xs space-y-2 text-slate-700">
                    @if($tiket->kategori_tiket === 'BACKBONE')
                        <p><strong class="text-slate-900">Segmen Backbone:</strong> {{ $tiket->backbone_segment }}</p>
                        <p><strong class="text-slate-900">Status Link Impact:</strong> <span class="font-bold text-red-600">{{ $tiket->status_link_impact }}</span></p>
                        <p><strong class="text-slate-900">Wilayah:</strong> {{ $tiket->wilayah }}</p>
                        <p><strong class="text-slate-900">Tipe Penanganan:</strong> {{ $tiket->tipe_penanganan }}</p>
                    @else
                        <p><strong class="text-slate-900">Nama Pelanggan:</strong> {{ $tiket->customer_name }}</p>
                        <p><strong class="text-slate-900">Customer ID (CID):</strong> {{ $tiket->customer_id ?? '-' }}
                            @if($tiket->is_ims_validated)
                                <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold">✓ Tervalidasi IMS</span>
                            @else
                                <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded font-bold">⚠️ Belum Tervalidasi IMS (AC-11)</span>
                            @endif
                        </p>
                        <p><strong class="text-slate-900">No Telp:</strong> {{ $tiket->customer_phone }}</p>
                        <p><strong class="text-slate-900">ODP / OLT:</strong> {{ $tiket->customer_odp ?? '-' }} / {{ $tiket->customer_olt ?? '-' }}</p>
                        <p><strong class="text-slate-900">Alamat:</strong> {{ $tiket->customer_address ?? '-' }}</p>
                    @endif
                    <p><strong class="text-slate-900">Dibuat Oleh:</strong> {{ $tiket->creator->name ?? '-' }} ({{ $tiket->tanggal_open ? $tiket->tanggal_open->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB)</p>
                    <p><strong class="text-slate-900">Respon Pertama:</strong> {{ $tiket->first_response_at ? $tiket->first_response_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : 'Belum ada' }}</p>
                </div>
            </div>

            <!-- Tim Teknisi Lapangan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="font-heading font-bold text-base text-slate-900">Tim Teknisi Ditugaskan</h3>
                    @can('ticket.dispatch')
                        <button type="button" onclick="switchTab('tab-actions')" class="text-xs text-blue-600 font-semibold hover:underline">Ubah Tim</button>
                    @endcan
                </div>
                <div class="space-y-2 text-xs">
                    @forelse($tiket->assignees as $tech)
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <span class="font-bold text-slate-800">{{ $tech->name }}</span>
                                <span class="text-slate-500 block text-[11px]">{{ $tech->employee_code }} · {{ $tech->phone }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $tech->pivot->role_in_team === 'lead' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ strtoupper($tech->pivot->role_in_team) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-400 italic">Belum ada teknisi yang di-dispatch untuk tiket ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Problem & Action Summary -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Resume Masalah &amp; Hasil Penanganan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Problem Temuan:</label>
                    <div class="p-3 bg-slate-50 rounded-xl border text-slate-800 min-h-[60px]">
                        {{ $tiket->problem_temuan ?? 'Belum ada catatan temuan.' }}
                    </div>
                </div>
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Action Taken:</label>
                    <div class="p-3 bg-slate-50 rounded-xl border text-slate-800 min-h-[60px]">
                        {{ $tiket->action_taken ?? 'Belum ada catatan tindakan penanganan.' }}
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-4 text-xs pt-2">
                <div><strong>Redaman Sebelum:</strong> {{ $tiket->redaman_sebelum !== null ? $tiket->redaman_sebelum . ' dBm' : '-' }}</div>
                <div><strong>Redaman Sesudah:</strong> {{ $tiket->redaman_sesudah !== null ? $tiket->redaman_sesudah . ' dBm' : '-' }}</div>
                <div><strong>Link Up:</strong> {!! $tiket->has_link_up ? '<span class="text-emerald-700 font-bold">✓ Terpulihkan</span>' : '<span class="text-red-600 font-bold">✗ Belum</span>' !!}</div>
            </div>
        </div>
    </div>

    <!-- TAB 2: KRONOLOGIS -->
    <div id="tab-chronology" class="tab-pane hidden space-y-6">
        <!-- Add Chronology Form (AC-05: only authorized technicians or Helpdesk/Admin) -->
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm space-y-3">
            <h3 class="font-heading font-bold text-base text-[#1E3A8A]">Tambah Entri Kronologis (Append-Only §7.3)</h3>
            <form method="POST" action="{{ route('tickets.chronologies.store', $tiket) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Aktivitas *</label>
                        <select name="category" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                            <option value="IZIN">IZIN (Izin Lokasi/Akses)</option>
                            <option value="OTDR">OTDR (Pengukuran Jarak/Loss)</option>
                            <option value="TRACING">TRACING (Penelusuran Kabel)</option>
                            <option value="MATERIAL">MATERIAL (Persiapan Barang)</option>
                            <option value="JOINTING">JOINTING (Penyambungan Core)</option>
                            <option value="LINK_UP">LINK_UP (Pemulihan Sinyal)</option>
                            <option value="SELESAI">SELESAI (Pekerjaan Lapangan Tuntas)</option>
                            <option value="LAIN">LAIN (Aktivitas Lainnya)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Foto (Geotag)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Video</label>
                        <input type="file" name="video" accept="video/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kronologis *</label>
                    <textarea name="message" rows="2" required placeholder="Tuliskan perkembangan pekerjaan di lapangan..." class="w-full text-xs rounded-xl border-slate-300 px-3 py-2"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400">Peringatan: Update wajib dilakukan minimal per 30 menit saat status PROSES.</span>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-sm">
                        Kirim Kronologis
                    </button>
                </div>
            </form>
        </div>

        <!-- Chronology Timeline Feed -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Riwayat Kronologis Pekerjaan</h3>
            <div class="space-y-4">
                @forelse($tiket->chronologies as $chron)
                    <div class="flex gap-3 text-xs border-b border-slate-100 pb-3">
                        <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ substr($chron->user->name ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">{{ $chron->user->name ?? 'Sistem' }}</span>
                                <span class="text-slate-400 text-[11px]">{{ $chron->created_at ? $chron->created_at->timezone('Asia/Jakarta')->format('d M, H:i') : '-' }} WIB</span>
                            </div>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $chron->category }}
                            </span>
                            <p class="text-slate-700 text-xs">{{ $chron->message }}</p>
                            @if($chron->photo_path)
                                <div class="mt-2">
                                    <a href="/storage/{{ $chron->photo_path }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 font-semibold">
                                        📷 Lihat Foto Dokumentasi
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 italic text-xs">Belum ada catatan kronologis.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: STOP CLOCK -->
    <div id="tab-stopclock" class="tab-pane hidden space-y-6">
        @if($tiket->activeStopClock)
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-4">
                <div>
                    <h4 class="font-bold text-amber-900 text-sm">⏸ Stop Clock Sedang Aktif!</h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Alasan: <strong>{{ $tiket->activeStopClock->reason }}</strong> — {{ $tiket->activeStopClock->reason_detail }}
                        (Mulai: {{ $tiket->activeStopClock->started_at->timezone('Asia/Jakarta')->format('d M H:i') }} WIB)
                    </p>
                </div>
                <form method="POST" action="{{ route('tickets.stop-clock.end', $tiket->activeStopClock) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm">
                        Hentikan Stop Clock
                    </button>
                </form>
            </div>
        @else
            <!-- Start Stop Clock Form (§7.4) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Mulai Jeda Stop Clock (§7.4)</h3>
                <form method="POST" action="{{ route('tickets.stop-clock.store', $tiket) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Stop Clock *</label>
                            <select name="reason" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                                <option value="IZIN_AKSES">IZIN_AKSES (Menunggu Izin Kawasan/Gedung - Wajib Bukti)</option>
                                <option value="PIHAK_KETIGA_PLN">PIHAK_KETIGA_PLN (Menunggu PLN/Pihak Ketiga - Wajib Bukti)</option>
                                <option value="CUACA_EKSTRIM">CUACA_EKSTRIM (Hujan Deras / Bencana)</option>
                                <option value="MENUNGGU_MATERIAL">MENUNGGU_MATERIAL (Pengambilan Logistik)</option>
                                <option value="PERMINTAAN_PELANGGAN">PERMINTAAN_PELANGGAN (Jadwal Ulang Pelanggan - Wajib Bukti)</option>
                                <option value="FORCE_MAJEURE">FORCE_MAJEURE (Kondisi Darurat Luar Biasa)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Bukti (Wajib untuk Izin / PLN / Pelanggan)</label>
                            <input type="file" name="evidence" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Detail Alasan Penjelasan *</label>
                        <textarea name="reason_detail" rows="2" required placeholder="Jelaskan kendala lapangan..." class="w-full text-xs rounded-xl border-slate-300 px-3 py-2"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-sm">
                            Aktifkan Stop Clock
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Stop Clock History Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Riwayat Jeda Stop Clock</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 border-b">
                        <tr>
                            <th class="p-2">Alasan</th>
                            <th class="p-2">Mulai</th>
                            <th class="p-2">Selesai</th>
                            <th class="p-2">Durasi</th>
                            <th class="p-2">Persetujuan</th>
                            <th class="p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tiket->stopClocks as $sc)
                            <tr>
                                <td class="p-2 font-medium text-slate-800">{{ $sc->reason }}<br><span class="text-slate-400 text-[10px]">{{ $sc->reason_detail }}</span></td>
                                <td class="p-2">{{ $sc->started_at ? $sc->started_at->timezone('Asia/Jakarta')->format('d M H:i') : '-' }}</td>
                                <td class="p-2">{{ $sc->ended_at ? $sc->ended_at->timezone('Asia/Jakarta')->format('d M H:i') : 'Aktif' }}</td>
                                <td class="p-2 font-bold">{{ $sc->duration_minutes ?? '-' }} Menit</td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sc->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($sc->approval_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ strtoupper($sc->approval_status) }}
                                    </span>
                                </td>
                                <td class="p-2">
                                    @can('ticket.stop_clock.approve')
                                        @if($sc->approval_status === 'pending')
                                            <div class="flex gap-1">
                                                <form method="POST" action="{{ route('tickets.stop-clock.approve', $sc) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded text-[10px] font-semibold">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('tickets.stop-clock.reject', $sc) }}">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Ditolak oleh atasan">
                                                    <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded text-[10px] font-semibold">Tolak</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-4 text-center text-slate-400">Tidak ada data Stop Clock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: ASET TEKNIS & MATERIAL -->
    <div id="tab-assets" class="tab-pane hidden space-y-6">
        <!-- Materials Section (§7.6) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b pb-2">
                <h3 class="font-heading font-bold text-base text-slate-900">Pemakaian Material / Logistik</h3>
            </div>
            <!-- Form pemakaian material -->
            <form method="POST" action="{{ route('tickets.materials.use', $tiket) }}" class="flex flex-wrap items-end gap-3 text-xs">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Material *</label>
                    <select name="material_id" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                        @foreach($materials as $mat)
                            <option value="{{ $mat->id }}">{{ $mat->nama }} (Tersedia: {{ $mat->stok_tersedia }} {{ $mat->satuan }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label class="block font-semibold text-slate-700 mb-1">Jumlah *</label>
                    <input type="number" name="quantity" min="1" value="1" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl">
                    + Pakai Material
                </button>
            </form>

            <div class="overflow-x-auto pt-2">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 border-b">
                        <tr>
                            <th class="p-2">Kode</th>
                            <th class="p-2">Nama Material</th>
                            <th class="p-2">Jumlah</th>
                            <th class="p-2">Pengguna</th>
                            <th class="p-2">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tiket->materials as $tMat)
                            <tr>
                                <td class="p-2 font-mono">{{ $tMat->material->kode }}</td>
                                <td class="p-2 font-medium">{{ $tMat->material->nama }}</td>
                                <td class="p-2 font-bold">{{ $tMat->quantity }} {{ $tMat->material->satuan }}</td>
                                <td class="p-2">{{ $tMat->user->name }}</td>
                                <td class="p-2 text-slate-400">{{ $tMat->created_at ? $tMat->created_at->timezone('Asia/Jakarta')->format('d M H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-3 text-center text-slate-400">Belum ada material yang dipakai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Titik Perbaikan & Joint Closure (§7.6) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Titik Perbaikan (Repair Points)</h3>
                <form method="POST" action="{{ route('tickets.repair-points.store', $tiket) }}" class="space-y-2 text-xs">
                    @csrf
                    <div>
                        <input type="text" name="nama_titik" placeholder="Nama Titik (mis. Tiang FO SRE-12)" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                    </div>
                    <div>
                        <input type="text" name="jenis_kerusakan" placeholder="Jenis Kerusakan (mis. Kabel putus tertabrak)" class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold">+ Tambah Titik</button>
                    </div>
                </form>
                <div class="space-y-1.5 pt-2">
                    @foreach($tiket->repairPoints as $rp)
                        <div class="p-2 bg-slate-50 rounded-xl border text-xs">
                            <strong class="text-slate-800">{{ $rp->nama_titik }}</strong> - {{ $rp->jenis_kerusakan ?? '-' }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Joint Closure &amp; Sambungan Core</h3>
                <form method="POST" action="{{ route('tickets.joint-closures.store', $tiket) }}" class="space-y-2 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="nama_closure" placeholder="Nama Closure (JC-01)" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                        <input type="number" name="kapasitas_core" value="24" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-3 py-1.5 bg-teal-600 text-white rounded-xl text-xs font-semibold">+ Tambah Closure</button>
                    </div>
                </form>
                <div class="space-y-3 pt-2">
                    @foreach($tiket->jointClosures as $jc)
                        <div class="p-2.5 bg-slate-50 rounded-xl border text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <strong class="text-slate-800">{{ $jc->nama_closure }} (Kapasitas: {{ $jc->kapasitas_core }} Core)</strong>
                                <span class="text-slate-500">{{ $jc->cores->count() }} sambungan</span>
                            </div>
                            <!-- Form input core sambungan -->
                            <form method="POST" action="{{ route('tickets.closure-cores.store', $jc) }}" class="grid grid-cols-5 gap-1.5 text-[11px]">
                                @csrf
                                <input type="text" name="tube_asal" placeholder="Tube Asal" required class="rounded border-slate-300 px-1.5 py-1">
                                <input type="number" name="core_asal" placeholder="Core Asal" required class="rounded border-slate-300 px-1.5 py-1">
                                <input type="text" name="tube_tujuan" placeholder="Tube Tujuan" required class="rounded border-slate-300 px-1.5 py-1">
                                <input type="number" name="core_tujuan" placeholder="Core Tujuan" required class="rounded border-slate-300 px-1.5 py-1">
                                <button type="submit" class="bg-blue-600 text-white rounded px-2 font-bold">Simpan</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: HANDOVER SHIFT -->
    <div id="tab-handover" class="tab-pane hidden space-y-6">
        <!-- Create Handover Form (§7.5) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Buat Handover Shift Kerja (§7.5)</h3>
            <form method="POST" action="{{ route('tickets.handovers.store', $tiket) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Shift Asal *</label>
                        <input type="text" name="shift_from" required placeholder="Pagi (08:15 - 17:00)" class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Shift Tujuan *</label>
                        <input type="text" name="shift_to" required placeholder="Siang (13:00 - 21:00)" class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Teknisi Penerima *</label>
                        <select name="recipient_id" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Handover Wajib *</label>
                    <textarea name="notes" rows="2" required placeholder="Tuliskan kendala yang masih perlu dilanjutkan oleh shift berikutnya..." class="w-full text-xs rounded-xl border-slate-300 px-3 py-2"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm">
                        Kirim Handover
                    </button>
                </div>
            </form>
        </div>

        <!-- Handover History List -->
        <div class="space-y-3">
            @forelse($tiket->handovers as $ho)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800">{{ $ho->shift_from }} &rarr; {{ $ho->shift_to }}</span>
                        <span class="text-slate-400">{{ $ho->created_at ? $ho->created_at->timezone('Asia/Jakarta')->format('d M H:i') : '-' }} WIB</span>
                    </div>
                    <p class="text-slate-600"><strong>Pengirim:</strong> {{ $ho->sender->name }} | <strong>Penerima:</strong> {{ $ho->recipient->name }}</p>
                    <p class="text-slate-800 bg-slate-50 p-2.5 rounded-xl border"><strong>Catatan:</strong> {{ $ho->notes }}</p>
                    @if($ho->auto_summary)
                        <p class="text-[11px] text-slate-500 italic">{{ $ho->auto_summary }}</p>
                    @endif
                    <div class="pt-1 flex items-center justify-between">
                        @if($ho->acknowledged_at)
                            <span class="text-emerald-700 font-bold">✓ Dikonfirmasi pada {{ $ho->acknowledged_at->timezone('Asia/Jakarta')->format('d M H:i') }} WIB</span>
                        @else
                            <span class="text-amber-700 font-bold">⏳ Menunggu konfirmasi penerima</span>
                            @if(auth()->id() === $ho->recipient_id)
                                <form method="POST" action="{{ route('tickets.handovers.acknowledge', $ho) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white font-bold rounded-lg text-xs">Konfirmasi Terima</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 bg-white rounded-2xl border text-center text-slate-400 text-xs">
                    Belum ada riwayat handover shift pada tiket ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 6: ALUR & CLOSING -->
    <div id="tab-actions" class="tab-pane hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Dispatch Form (§7.2) -->
            @can('ticket.dispatch')
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Dispatch Tim Teknisi (§7.2)</h3>
                <form method="POST" action="{{ route('tickets.dispatch', $tiket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Teknisi Lead (Penanggung Jawab) *</label>
                        <select name="lead_id" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}" {{ $lead && $lead->id === $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Anggota Tim Teknisi</label>
                        <div class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto p-2 bg-slate-50 rounded-xl border">
                            @foreach($technicians as $t)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="checkbox" name="member_ids[]" value="{{ $t->id }}"
                                        {{ $tiket->assignees->contains($t->id) ? 'checked' : '' }} class="rounded text-blue-600">
                                    <span class="text-[11px]">{{ $t->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
            @endcan

            <!-- 2. Closing Awal Teknisi (AC-06, §7.2, §7.6) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Tahap 1: Pengajuan Closing Awal</h3>
                <p class="text-xs text-slate-500">
                    Diajukan oleh teknisi lapangan setelah perbaikan selesai. Sistem memverifikasi prasyarat dokumentasi &amp; redaman (AC-06).
                </p>
                <form method="POST" action="{{ route('tickets.closing-awal', $tiket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Problem Temuan *</label>
                        <input type="text" name="problem_temuan" value="{{ $tiket->problem_temuan }}" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Action Taken *</label>
                        <input type="text" name="action_taken" value="{{ $tiket->action_taken }}" required class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Redaman Sesudah (dBm)</label>
                            <input type="number" step="0.01" name="redaman_sesudah" value="{{ $tiket->redaman_sesudah }}" class="w-full text-xs rounded-xl border-slate-300 px-3 py-2">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="has_link_up" value="1" {{ $tiket->has_link_up ? 'checked' : '' }} class="rounded text-green-600">
                                <span class="font-semibold text-slate-800">Konfirmasi Link Up</span>
                            </label>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl">
                            Ajukan Closing Awal
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. Verifikasi & Closing Akhir Helpdesk (AC-08, §7.2, §7.7) -->
            @can('ticket.verify_close')
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Tahap 2: Verifikasi &amp; Closing Akhir Helpdesk</h3>
                <p class="text-xs text-slate-500">
                    Memverifikasi hasil kerja teknisi dan menutup tiket secara permanen, menghitung MTTR bersih dan status SLA (AC-08).
                </p>
                <div class="flex items-center gap-2 pt-2">
                    <form method="POST" action="{{ route('tickets.verify-close', $tiket) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm">
                            ✓ Verifikasi &amp; Tutup Tiket (CLOSE)
                        </button>
                    </form>

                    <form method="POST" action="{{ route('tickets.reject-closing', $tiket) }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="reason" value="Hasil pekerjaan belum lengkap / redaman belum memenuhi standar">
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-sm">
                            ✗ Tolak Closing (Reject)
                        </button>
                    </form>
                </div>
            </div>
            @endcan

            <!-- 4. Reopen & Pembatalan Tiket (§7.2) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="font-heading font-bold text-base text-slate-900 border-b pb-2">Kontrol Tiket (Reopen / Batalkan)</h3>
                <div class="space-y-3">
                    @if($tiket->status === 'CLOSE')
                        <form method="POST" action="{{ route('tickets.reopen', $tiket) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Alasan reopen tiket..." required class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                            <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl">
                                Reopen Tiket (Batas 7 Hari)
                            </button>
                        </form>
                    @elseif(in_array($tiket->status, ['OPEN', 'PROSES']))
                        <form method="POST" action="{{ route('tickets.cancel', $tiket) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Alasan pembatalan tiket..." required class="w-full text-xs rounded-xl border-slate-300 px-3 py-1.5">
                            <button type="submit" class="px-4 py-1.5 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs rounded-xl">
                                Batalkan Tiket (CANCELLED)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-slate-500');
    });

    const activePane = document.getElementById(tabId);
    if (activePane) {
        activePane.classList.remove('hidden');
    }
    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-blue-600', 'text-blue-600');
    }
}
</script>
@endsection

