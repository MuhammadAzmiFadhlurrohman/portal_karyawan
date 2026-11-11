<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('gomsn.pengajuan.index') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1 mb-1">
                    &larr; Kembali ke Daftar Pengajuan
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-heading font-bold text-[#1E3A8A] tracking-tight">
                        {{ $pengajuan->nomor_pengajuan }}
                    </h1>
                    @php
                        $badge = match($pengajuan->status) {
                            'draft' => 'bg-slate-100 text-slate-700',
                            'menunggu_atasan' => 'bg-amber-100 text-amber-800',
                            'menunggu_finance' => 'bg-blue-100 text-blue-800',
                            'menunggu_executive' => 'bg-purple-100 text-purple-800',
                            'dicairkan' => 'bg-cyan-100 text-cyan-800',
                            'menunggu_bukti' => 'bg-orange-100 text-orange-800',
                            'selesai' => 'bg-emerald-100 text-emerald-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            default => 'bg-slate-100 text-slate-700'
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badge }}">
                        {{ strtoupper(str_replace('_', ' ', $pengajuan->status)) }}
                    </span>
                    @php
                        $jenisBadge = match($pengajuan->jenis) {
                            'STOK' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'BELI' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                            'PERMINTAAN', 'BARANG' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                            default => 'bg-amber-100 text-amber-800 border-amber-300',
                        };
                        $jenisText = match($pengajuan->jenis) {
                            'STOK' => '📦 STOK GUDANG',
                            'BELI' => '🛒 PENGADAAN (BELI)',
                            'PERMINTAAN', 'BARANG' => '📄 PERMINTAAN BARANG',
                            default => '💵 KASBON DANA',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $jenisBadge }}">
                        {{ $jenisText }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Diajukan oleh <strong class="text-slate-700">{{ $pengajuan->user->name }}</strong> ({{ $pengajuan->user->employee_code }}) pada {{ $pengajuan->created_at->format('d F Y H:i') }}
                    @if ($pengajuan->atasan)
                    · Ditujukan kepada Atasan: <strong class="text-[#1E3A8A]">{{ $pengajuan->atasan->name }} ({{ $pengajuan->atasan->employee_code }})</strong>
                    @endif
                </p>
            </div>

            <div class="flex flex-col items-start sm:items-end gap-2">
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-500 block">
                        {{ ($pengajuan->jenis === 'STOK' || $pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG') ? 'Jenis Permintaan:' : 'Total Nominal:' }}
                    </span>
                    <span class="text-xl sm:text-2xl font-heading font-bold text-[#1E3A8A]">
                        @if ($pengajuan->jenis === 'STOK')
                            <span class="text-emerald-700 text-base sm:text-lg">Stok Gudang (Diatur Finance)</span>
                        @elseif ($pengajuan->jenis === 'PERMINTAAN' || $pengajuan->jenis === 'BARANG')
                            <span class="text-cyan-700 text-base sm:text-lg">Permintaan Barang</span>
                        @else
                            Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                        @endif
                    </span>
                </div>

                @if ($pengajuan->user_id === auth()->id() && in_array($pengajuan->status, ['draft', 'menunggu_atasan']) && ! $pengajuan->approvals()->where('action', 'approved')->exists())
                <a href="{{ route('gomsn.pengajuan.edit', $pengajuan->id) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Edit Pengajuan</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Overdue Warning on this Submission -->
        @if ($pengajuan->status === 'dicairkan' && $pengajuan->realisasi_deadline && \Carbon\Carbon::now()->greaterThan($pengajuan->realisasi_deadline))
        <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-6 h-6 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong>Melewati Batas Waktu Realisasi (7 Hari):</strong> Batas waktu unggah bukti adalah {{ $pengajuan->realisasi_deadline->format('d M Y') }}. Harap segera unggah nota pengeluaran realisasi.
            </div>
        </div>
        @endif

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Info & Items -->
            <div class="md:col-span-2 space-y-6">
                <!-- Overview Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">
                        Ringkasan Permohonan
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Jenis Permohonan:</span>
                            <span class="font-bold text-slate-800">
                                @if ($pengajuan->jenis === 'STOK')
                                    📦 Permintaan Barang Stok Gudang
                                @elseif ($pengajuan->jenis === 'BELI')
                                    🛒 Pengadaan / Pembelian Barang Baru
                                @else
                                    💵 Pengajuan Kasbon Dana Operasional
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Pengelola / PIC:</span>
                            <span class="font-semibold text-slate-800">
                                {{ $pengajuan->jenis === 'STOK' ? 'Bagian Finance (Pengatur Stok)' : ($pengajuan->metode_pembayaran ?? 'Finance (Pencairan Kas)') }}
                            </span>
                        </div>
                        @if ($pengajuan->jenis !== 'STOK' && $pengajuan->metode_pembayaran === 'TRANSFER')
                        <div class="col-span-2 p-3 rounded-lg bg-blue-50 text-blue-900 text-xs">
                            <span class="font-bold block mb-0.5">Rekening Pencairan:</span>
                            {{ $pengajuan->bank_name }} - {{ $pengajuan->account_number }} a/n {{ $pengajuan->account_holder }}
                        </div>
                        @endif
                        @if ($pengajuan->jenis === 'STOK')
                        <div class="col-span-2 p-3 rounded-lg bg-emerald-50 text-emerald-900 text-xs border border-emerald-200">
                            <strong class="block mb-0.5 font-semibold text-emerald-800">Pengaturan Stok Gudang oleh Finance:</strong>
                            Permintaan ini mengambil material dari persediaan gudang internal perusahaan. Tidak ada transfer uang tunai; pengeluaran fisik barang disetujui &amp; dicatat langsung oleh bagian Finance.
                        </div>
                        @endif
                        <div class="col-span-2">
                            <span class="text-slate-500 block mb-1">Keperluan / Keterangan:</span>
                            <p class="p-3 bg-slate-50 rounded-xl text-slate-800 whitespace-pre-line leading-relaxed">
                                {{ $pengajuan->keperluan }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Table Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">
                        Rincian Item ({{ $pengajuan->details->count() }})
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                                <tr>
                                    <th class="py-2.5 px-3">Item / Uraian</th>
                                    <th class="py-2.5 px-3 text-center">Qty Diminta</th>
                                    @if ($pengajuan->jenis === 'STOK')
                                    <th class="py-2.5 px-3 text-right">Stok Gudang (Tersedia)</th>
                                    @else
                                    <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($pengajuan->details as $item)
                                <tr>
                                    <td class="py-2.5 px-3 font-medium text-slate-800">
                                        {{ $item->item_name }}
                                        @if ($item->material)
                                            <span class="text-[10px] text-slate-500 block font-mono">Kode: {{ $item->material->kode }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-slate-700 font-semibold">
                                        {{ $item->qty }} {{ $item->satuan }}
                                    </td>
                                    @if ($pengajuan->jenis === 'STOK')
                                    <td class="py-2.5 px-3 text-right">
                                        @if ($item->material)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item->material->stok_tersedia >= $item->qty ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->material->stok_tersedia }} {{ $item->material->satuan }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Material manual</span>
                                        @endif
                                    </td>
                                    @else
                                    <td class="py-2.5 px-3 text-right text-slate-600">
                                        Rp {{ number_format((float) $item->estimated_unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-semibold text-slate-800">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            @if ($pengajuan->jenis !== 'STOK')
                            <tfoot class="bg-slate-50 border-t border-slate-200 font-bold">
                                <tr>
                                    <td colspan="3" class="py-2.5 px-3 text-right text-slate-600">TOTAL ESTIMASI:</td>
                                    <td class="py-2.5 px-3 text-right text-[#1B2A41]">
                                        Rp {{ number_format((float) $pengajuan->total_nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Realization Info if Exists -->
                @if ($pengajuan->realization_amount !== null || $pengajuan->realization_proof_path)
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-slate-800 border-b border-slate-100 pb-2 flex items-center justify-between">
                        <span>Laporan Realisasi &amp; Bukti Nota</span>
                        <span class="text-xs font-normal text-slate-500">
                            Diserahkan: {{ $pengajuan->realized_at?->format('d M Y H:i') }}
                        </span>
                    </h3>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Total Realisasi Pengeluaran:</span>
                            <span class="text-base font-bold text-emerald-700">
                                Rp {{ number_format((float) $pengajuan->realization_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Selisih Pengembalian / Kurang:</span>
                            @php
                                $diff = (float)$pengajuan->total_nominal - (float)$pengajuan->realization_amount;
                            @endphp
                            <span class="text-base font-bold {{ $diff >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                                Rp {{ number_format(abs($diff), 0, ',', '.') }} ({{ $diff >= 0 ? 'Lebih/Kembali' : 'Kurang' }})
                            </span>
                        </div>
                        @if ($pengajuan->realization_notes)
                        <div class="col-span-2">
                            <span class="text-slate-500 block mb-0.5">Catatan Realisasi:</span>
                            <p class="p-2.5 bg-slate-50 rounded-lg text-slate-700">{{ $pengajuan->realization_notes }}</p>
                        </div>
                        @endif
                        @if ($pengajuan->realization_proof_path)
                        <div class="col-span-2 pt-1">
                            <span class="text-slate-500 block text-xs font-semibold mb-2">Lampiran Bukti Nota / Foto Kwitansi:</span>
                            @if (!str_ends_with(strtolower($pengajuan->realization_proof_path), '.pdf'))
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 p-2 max-w-md">
                                <a href="/storage/{{ $pengajuan->realization_proof_path }}" target="_blank" class="block group relative overflow-hidden rounded-lg">
                                    <img src="/storage/{{ $pengajuan->realization_proof_path }}" alt="Bukti Nota" class="max-h-72 w-auto object-contain mx-auto group-hover:scale-105 transition duration-200">
                                    <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs font-bold gap-1">
                                        <span>Buka Ukuran Penuh ↗</span>
                                    </div>
                                </a>
                            </div>
                            @else
                            <a href="/storage/{{ $pengajuan->realization_proof_path }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-semibold">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Buka Lampiran Dokumen Bukti (PDF)</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Col: Approvals & Workflow Actions -->
            <div class="space-y-6">
                <!-- Action Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-[#1E3A8A] border-b border-slate-100 pb-2">
                        Tindakan Alur Kerja
                    </h3>

                    <!-- 0. Requester Edit Action (Available while not yet approved) -->
                    @if ($pengajuan->user_id === auth()->id() && in_array($pengajuan->status, ['draft', 'menunggu_atasan']) && ! $pengajuan->approvals()->where('action', 'approved')->exists())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                        <div class="flex items-center gap-1.5 text-amber-900 text-xs font-bold">
                            <span>✏️</span>
                            <span>Perlu merevisi data pengajuan?</span>
                        </div>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            Pengajuan belum disetujui oleh Atasan maupun Finance. Anda masih dapat mengedit jenis, rincian barang, nominal, atau keperluan.
                        </p>
                        <a href="{{ route('gomsn.pengajuan.edit', $pengajuan->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>Edit Formulir Pengajuan Ini</span>
                        </a>
                    </div>
                    @endif

                    <!-- 1. Submit Draft -->
                    @if ($pengajuan->status === 'draft' && auth()->id() === $pengajuan->user_id)
                    <form method="POST" action="{{ route('gomsn.pengajuan.submit', $pengajuan->id) }}">
                        @csrf
                        <p class="text-xs text-slate-600 mb-3">
                            Formulir pengajuan masih berupa draft. Klik tombol di bawah untuk meneruskan ke atasan.
                        </p>
                        <button type="submit" class="btn-primary w-full text-xs py-2.5 justify-center">
                            Ajukan ke Atasan &rarr;
                        </button>
                    </form>
                    @endif

                    <!-- 2. Atasan Approval (AC-10: non-self) -->
                    @if ($pengajuan->status === 'menunggu_atasan')
                        @php
                            $isTargetAtasan = auth()->user()->hasRole(['super_admin', 'admin']) 
                                || ! $pengajuan->atasan_id 
                                || $pengajuan->atasan_id === auth()->id();
                        @endphp
                        @if (auth()->id() !== $pengajuan->user_id && (auth()->user()->can('gomsn.pengajuan.approve_atasan') || auth()->user()->hasRole(['super_admin', 'atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'hrd', 'ceo'])) && $isTargetAtasan)
                        <div class="space-y-3">
                            <p class="text-xs text-slate-600">
                                Persetujuan Atasan Langsung untuk verifikasi urgensi kebutuhan operasional.
                            </p>
                            <form method="POST" action="{{ route('gomsn.pengajuan.approve-atasan', $pengajuan->id) }}">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="notes" placeholder="Catatan persetujuan (opsional)" class="w-full text-xs rounded-lg border-slate-300 py-1.5" />
                                </div>
                                <button type="submit" class="btn-primary w-full text-xs py-2 justify-center bg-emerald-600 hover:bg-emerald-700">
                                    ✓ Setujui Pengajuan
                                </button>
                            </form>

                            <form method="POST" action="{{ route('gomsn.pengajuan.reject', $pengajuan->id) }}">
                                @csrf
                                <input type="hidden" name="stage" value="atasan">
                                <div class="mb-2">
                                    <input type="text" name="reason" required placeholder="Alasan penolakan..." class="w-full text-xs rounded-lg border-slate-300 py-1.5" />
                                </div>
                                <button type="submit" class="w-full text-xs py-2 text-red-600 border border-red-200 hover:bg-red-50 rounded-xl font-semibold transition">
                                    ✕ Tolak Pengajuan
                                </button>
                            </form>
                        </div>
                        @elseif ($pengajuan->atasan_id && $pengajuan->atasan_id !== auth()->id())
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs space-y-1">
                            <strong class="font-bold block">Persetujuan Terbatas:</strong>
                            <p class="text-amber-800">
                                Pengajuan ini ditujukan secara spesifik kepada atasan: <strong>{{ $pengajuan->atasan?->name ?? 'Atasan lain' }}</strong>. Anda tidak berwenang menyetujui pengajuan milik atasan lain.
                            </p>
                        </div>
                        @else
                        <p class="text-xs text-slate-500 italic">
                            Menunggu persetujuan atasan langsung. Pemohon dilarang menyetujui pengajuannya sendiri (SOP §8.1).
                        </p>
                        @endif
                    @endif

                    <!-- 3. Executive Approval (> Rp 5jt) -->
                    @if ($pengajuan->status === 'menunggu_executive')
                        @if (auth()->id() !== $pengajuan->user_id && auth()->user()->hasRole(['super_admin', 'ceo']))
                        <div class="space-y-3">
                            <div class="p-2.5 rounded-lg bg-purple-50 text-purple-900 text-xs font-semibold">
                                Otorisasi Direktur/CEO (> Rp 5.000.000)
                            </div>
                            <form method="POST" action="{{ route('gomsn.pengajuan.approve-executive', $pengajuan->id) }}">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="notes" placeholder="Catatan approval Direktur..." class="w-full text-xs rounded-lg border-slate-300 py-1.5" />
                                </div>
                                <button type="submit" class="btn-primary w-full text-xs py-2 justify-center bg-purple-600 hover:bg-purple-700">
                                    ✓ Otorisasi Direktur
                                </button>
                            </form>
                        </div>
                        @else
                        <p class="text-xs text-slate-500 italic">
                            Menunggu otorisasi Direktur/CEO karena nominal melebihi batas Rp 5.000.000.
                        </p>
                        @endif
                    @endif

                    <!-- 4. Finance Disbursement / Stock Release -->
                    @if ($pengajuan->status === 'menunggu_finance')
                        @if (auth()->id() !== $pengajuan->user_id && auth()->user()->hasRole(['super_admin', 'finance']))
                        <div class="space-y-3">
                            @if ($pengajuan->jenis === 'STOK')
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs space-y-1">
                                <strong class="block font-semibold text-emerald-800">📦 Otorisasi Stok Gudang (Finance)</strong>
                                <p class="text-emerald-700">
                                    Sebagai pengatur stok, Finance memeriksa ketersediaan fisik barang di gudang. Mengonfirmasi tombol di bawah akan otomatis <strong>memotong kuantitas stok di sistem</strong> dan menandai pengajuan <strong>SELESAI</strong>.
                                </p>
                            </div>
                            <form method="POST" action="{{ route('gomsn.pengajuan.disburse', $pengajuan->id) }}" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Catatan Penyerahan Barang (Opsional)</label>
                                    <input type="text" name="notes" placeholder="Contoh: Barang telah diserahkan lengkap di gudang pusat" class="w-full text-xs rounded-lg border-slate-300 py-1.5" />
                                </div>
                                <button type="submit" class="btn-accent w-full text-xs py-2 justify-center">
                                    ✓ Keluarkan dari Stok &amp; Serahkan Barang
                                </button>
                            </form>
                            @else
                            <p class="text-xs text-slate-600">
                                Verifikasi saldo dan pencairan dana {{ $pengajuan->jenis === 'BELI' ? 'pembelian barang baru' : 'kasbon operasional' }} ke pemohon.
                            </p>
                            <form method="POST" action="{{ route('gomsn.pengajuan.disburse', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Unggah Bukti Transfer / Kwitansi Kasir <span class="text-red-500">*</span></label>
                                    <input type="file" name="bukti_transfer" required accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                </div>
                                <button type="submit" class="btn-primary w-full text-xs py-2 justify-center">
                                    ✓ Konfirmasi Pencairan Dana
                                </button>
                            </form>
                            @endif
                        </div>
                        @else
                        <p class="text-xs text-slate-500 italic">
                            {{ $pengajuan->jenis === 'STOK' ? 'Menunggu verifikasi dan penyerahan barang stok oleh bagian Finance.' : 'Menunggu verifikasi dan pencairan kas oleh bagian Finance.' }}
                        </p>
                        @endif
                    @endif

                    <!-- 5. Realization Submission (By Requester) -->
                    @if ($pengajuan->status === 'dicairkan' && auth()->id() === $pengajuan->user_id)
                    <div class="space-y-3">
                        <div class="p-3 bg-cyan-50 border border-cyan-200 rounded-xl text-cyan-900 text-xs">
                            Dana telah dicairkan! Harap laporkan nota pengeluaran sebelum batas waktu 7 hari.
                        </div>
                        <form method="POST" action="{{ route('gomsn.pengajuan.realize', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Total Realisasi Pengeluaran (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="realization_amount" required min="0" placeholder="Contoh: 450000" class="w-full text-xs rounded-lg border-slate-300 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Catatan Realisasi</label>
                                <textarea name="realization_notes" rows="2" placeholder="Keterangan pengeluaran..." class="w-full text-xs rounded-lg border-slate-300 py-1.5"></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Unggah Foto / PDF Nota Kwitansi</label>
                                <input type="file" name="realization_proof" accept="image/*,application/pdf" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>
                            <button type="submit" class="btn-primary w-full text-xs py-2 justify-center bg-cyan-700 hover:bg-cyan-800">
                                Kirim Laporan Realisasi
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($pengajuan->status === 'selesai')
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-800 text-xs flex items-center gap-2">
                        <span>✓</span>
                        <span>Pengajuan telah selesai dan seluruh pertanggungjawaban terverifikasi.</span>
                    </div>
                    @endif

                    @if ($pengajuan->status === 'ditolak')
                    <div class="p-3 bg-red-50 rounded-xl text-red-800 text-xs">
                        <strong>Ditolak:</strong> {{ $pengajuan->approvals->where('status', 'rejected')->last()?->notes ?? 'Tidak disetujui.' }}
                    </div>
                    @endif
                </div>

                <!-- Approval Audit Log Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="font-heading font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">
                        Riwayat Persetujuan
                    </h3>

                    <div class="space-y-3">
                        @forelse ($pengajuan->approvals as $appr)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">{{ $appr->approver->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $appr->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] uppercase font-semibold text-slate-500">{{ $appr->stage }}</span>
                                <span class="text-[10px] font-bold {{ $appr->status === 'approved' ? 'text-emerald-700' : 'text-red-700' }}">
                                    [{{ strtoupper($appr->status) }}]
                                </span>
                            </div>
                            @if ($appr->notes)
                            <p class="text-slate-600 italic">"{{ $appr->notes }}"</p>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic">Belum ada catatan persetujuan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

