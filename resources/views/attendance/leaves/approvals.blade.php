<x-layouts.app title="Persetujuan Cuti &amp; Izin">
    <div class="space-y-4 w-full pb-16" x-data="{ rejectModalOpen: false, selectedLeaveId: null, rejectActionUrl: '' }">
        
        <!-- 1. Header Banner (Compact, Sleek Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/25 shadow-md p-4 sm:py-4 sm:px-6 select-none"
             style="background: var(--gomsn-header-bg) !important;">
            
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                        <svg class="w-5 h-5 text-cyan-300 drop-shadow-[0_0_6px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-heading text-sm sm:text-base font-bold text-white tracking-tight leading-snug truncate">
                            Persetujuan Cuti &amp; Izin
                        </h1>
                        <p class="text-[11px] sm:text-xs text-teal-100/80 leading-snug truncate">
                            Verifikasi pengajuan cuti tahunan, sakit, dan izin karyawan
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-200 border border-teal-300/30 shrink-0">
                    Khusus HRD
                </span>
            </div>
        </div>

        <!-- 2. Status Filter Tabs (Sleek Horizontal Scrollable Pills - 1 Single Compact Row) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 px-0.5 -mx-0.5">
            <!-- Menunggu -->
            <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
               class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer {{ $status === 'pending' ? 'bg-[#155E63] text-white shadow-sm ring-1 ring-[#155E63]' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 shadow-2xs' }}">
                <span class="w-2 h-2 rounded-full {{ $status === 'pending' ? 'bg-amber-400 animate-pulse' : 'bg-amber-500' }}"></span>
                <span>Menunggu</span>
                @if ($pendingCount > 0)
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'pending' ? 'bg-white text-[#155E63]' : 'bg-amber-100 text-amber-900' }}">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <!-- Disetujui -->
            <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
               class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer {{ $status === 'approved' ? 'bg-[#155E63] text-white shadow-sm ring-1 ring-[#155E63]' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 shadow-2xs' }}">
                <svg class="w-3.5 h-3.5 {{ $status === 'approved' ? 'text-emerald-300' : 'text-emerald-600' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Disetujui</span>
                @if ($approvedCount > 0)
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'approved' ? 'bg-white text-[#155E63]' : 'bg-slate-100 text-slate-700' }}">
                        {{ $approvedCount }}
                    </span>
                @endif
            </a>

            <!-- Ditolak -->
            <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
               class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer {{ $status === 'rejected' ? 'bg-[#155E63] text-white shadow-sm ring-1 ring-[#155E63]' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 shadow-2xs' }}">
                <svg class="w-3.5 h-3.5 {{ $status === 'rejected' ? 'text-rose-300' : 'text-rose-600' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Ditolak</span>
                @if ($rejectedCount > 0)
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'rejected' ? 'bg-white text-[#155E63]' : 'bg-slate-100 text-slate-700' }}">
                        {{ $rejectedCount }}
                    </span>
                @endif
            </a>

            <!-- Semua Data -->
            <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
               class="inline-flex items-center gap-1.5 py-2 px-3.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer {{ $status === 'all' ? 'bg-[#155E63] text-white shadow-sm ring-1 ring-[#155E63]' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 shadow-2xs' }}">
                <span>Semua Data</span>
            </a>
        </div>

        <!-- 3. Filter & Search Toolbar (Compact & Clean) -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-3 shadow-2xs">
            <form method="GET" action="{{ route('attendance.leaves.approvals') }}" class="space-y-2">
                <input type="hidden" name="status" value="{{ $status }}">
                
                <!-- Search Input with Enter to Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Cari nama karyawan, NIK, atau alasan..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50/70 hover:bg-slate-50 focus:bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 transition">
                </div>

                <!-- Dropdowns row: Divisi, Jenis, and Reset button -->
                <div class="flex items-center gap-2">
                    <!-- Divisi -->
                    <div class="relative flex-1">
                        <select name="divisi" onchange="this.form.submit()" 
                                class="w-full py-1.5 pl-2.5 pr-7 bg-slate-50/70 hover:bg-slate-50 focus:bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-600 transition appearance-none cursor-pointer">
                            <option value="all">Semua Divisi</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}" {{ $divisi === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Jenis Pengajuan -->
                    <div class="relative flex-1">
                        <select name="type" onchange="this.form.submit()" 
                                class="w-full py-1.5 pl-2.5 pr-7 bg-slate-50/70 hover:bg-slate-50 focus:bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-600 transition appearance-none cursor-pointer">
                            <option value="all">Semua Jenis</option>
                            <option value="annual" {{ $leaveType === 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sick" {{ $leaveType === 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                            <option value="special" {{ $leaveType === 'special' ? 'selected' : '' }}>Izin Khusus</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    @if ($search || $divisi !== 'all' || $leaveType !== 'all')
                        <a href="{{ route('attendance.leaves.approvals', ['status' => $status]) }}" 
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition shrink-0" 
                           title="Reset Filter">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 4. Content Area -->
        @if ($leaves->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white/80 p-10 flex flex-col items-center justify-center text-center min-h-[240px]">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800 mb-1">
                    @if ($status === 'pending')
                        Tidak Ada Antrean Pengajuan Absen
                    @elseif ($status === 'approved')
                        Belum Ada Riwayat Pengajuan Disetujui
                    @elseif ($status === 'rejected')
                        Belum Ada Riwayat Pengajuan Ditolak
                    @else
                        Tidak Ada Data Pengajuan Ditemukan
                    @endif
                </h3>
                <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                    @if ($status === 'pending')
                        Semua pengajuan cuti, izin, dan sakit dari karyawan telah selesai ditinjau.
                    @else
                        Riwayat pengajuan cuti dan izin karyawan akan tampil di sini.
                    @endif
                </p>
            </div>
        @else
            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200/80 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4">Karyawan</th>
                                <th class="py-3.5 px-3">Jenis Pengajuan</th>
                                <th class="py-3.5 px-3">Periode &amp; Durasi</th>
                                <th class="py-3.5 px-3">Alasan</th>
                                <th class="py-3.5 px-3">Lampiran / Bukti</th>
                                <th class="py-3.5 px-3">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($leaves as $item)
                                @php
                                    $emp = $item->user;
                                    $isPending = in_array($item->status, ['pending_hrd', 'pending_supervisor']);
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <!-- Karyawan -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] font-black flex items-center justify-center text-xs shrink-0 overflow-hidden shadow-2xs">
                                                @if ($emp?->avatar_url)
                                                    <img src="{{ $emp->avatar_url }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <span>{{ strtoupper(substr($emp?->name ?? 'U', 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $emp?->name }}</span>
                                                    <span class="text-[11px] font-mono text-slate-400">({{ $emp?->employee_code }})</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                                    <span class="font-semibold text-teal-700">{{ $emp?->division ?? '-' }}</span>
                                                    <span>&bull;</span>
                                                    <span>{{ $item->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Jenis Pengajuan -->
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        @if ($item->leave_type === 'annual')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                Cuti Tahunan
                                            </span>
                                        @elseif ($item->leave_type === 'sick')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Cuti Sakit
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                Izin Khusus
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Periode & Durasi -->
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                                            <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>{{ $item->start_date->format('d M') }} &mdash; {{ $item->end_date->format('d M Y') }}</span>
                                        </div>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-teal-50 text-teal-800 border border-teal-200/80 text-[10px] font-bold">
                                            {{ $item->working_days }} Hari Kerja
                                        </span>
                                    </td>

                                    <!-- Alasan Pengajuan -->
                                    <td class="py-3.5 px-3 max-w-xs">
                                        <p class="text-slate-700 text-xs leading-snug line-clamp-2" title="{{ $item->reason }}">
                                            "{{ $item->reason }}"
                                        </p>
                                    </td>

                                    <!-- Lampiran / Bukti -->
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        @if ($item->attachment_path)
                                            <a href="{{ route('attendance.leaves.attachment', $item) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-teal-50 hover:bg-teal-100/80 text-teal-700 font-bold border border-teal-200 text-[11px] transition">
                                                <svg class="w-3.5 h-3.5 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                <span>Bukti Surat &rarr;</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-[11px] italic">Tidak ada lampiran</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        @if ($isPending)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu HRD
                                            </span>
                                        @elseif ($item->status === 'approved')
                                            <div>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Disetujui HRD
                                                </span>
                                                <div class="text-[10px] text-slate-500 mt-0.5">
                                                    {{ $item->hrdApprover?->name ?? 'HRD' }} &bull; {{ $item->hrd_approved_at?->translatedFormat('d M Y') ?? '-' }}
                                                </div>
                                            </div>
                                        @elseif ($item->status === 'rejected')
                                            <div>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-300">
                                                    <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Ditolak
                                                </span>
                                                @if ($item->rejected_reason)
                                                    <div class="text-[10px] text-rose-600 italic max-w-[150px] truncate mt-0.5" title="{{ $item->rejected_reason }}">
                                                        "{{ $item->rejected_reason }}"
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        @if ($isPending)
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                <button type="button" 
                                                        @click="rejectActionUrl = '{{ route('attendance.leaves.reject', $item) }}'; selectedLeaveId = {{ $item->id }}; rejectModalOpen = true"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition shadow-2xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <span>Tolak</span>
                                                </button>

                                                <form method="POST" action="{{ route('attendance.leaves.approve-hrd', $item) }}" class="inline-block">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti/izin ini? Saldo cuti akan disesuaikan otomatis.')"
                                                            class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-2xs active:scale-[0.98] cursor-pointer">
                                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Setujui</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs font-medium italic">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View (md:hidden - Tailored for Smartphone Screens) -->
            <div class="md:hidden space-y-3">
                @foreach ($leaves as $item)
                    @php
                        $emp = $item->user;
                        $isPending = in_array($item->status, ['pending_hrd', 'pending_supervisor']);
                    @endphp
                    <div class="rounded-2xl border border-slate-200/90 bg-white p-3.5 sm:p-4 shadow-xs space-y-3 relative overflow-hidden transition-all">
                        
                        <!-- Top Accent Line -->
                        <div class="absolute inset-x-0 top-0 h-[3px] {{ $isPending ? 'bg-amber-400' : ($item->status === 'approved' ? 'bg-emerald-500' : 'bg-rose-400') }}"></div>

                        <!-- 1. Header: Avatar + Nama + Status Badge di Kanan Atas -->
                        <div class="flex items-start justify-between gap-2 pt-0.5">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 text-[#155E63] font-black flex items-center justify-center text-xs shrink-0 overflow-hidden shadow-2xs">
                                    @if ($emp?->avatar_url)
                                        <img src="{{ $emp->avatar_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ strtoupper(substr($emp?->name ?? 'U', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm text-slate-900 truncate leading-snug">
                                        {{ $emp?->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10.5px] text-slate-500 leading-none mt-0.5">
                                        <span class="font-semibold text-teal-700">{{ $emp?->division ?? '-' }}</span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="font-mono text-slate-400">{{ $emp?->employee_code }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Pill at Top Right -->
                            <div class="shrink-0">
                                @if ($isPending)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu HRD</span>
                                    </span>
                                @elseif ($item->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-800 border border-rose-300">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 2. Structured Information Card -->
                        <div class="rounded-xl bg-slate-50/90 border border-slate-200/70 p-3 space-y-2.5 text-xs">
                            <!-- Leave Type & Period Row -->
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <!-- Type Badge -->
                                <div>
                                    @if ($item->leave_type === 'annual')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold bg-teal-100/70 text-teal-800 border border-teal-200">
                                            Cuti Tahunan
                                        </span>
                                    @elseif ($item->leave_type === 'sick')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold bg-amber-100/80 text-amber-900 border border-amber-200">
                                            Cuti Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold bg-indigo-100/70 text-indigo-900 border border-indigo-200">
                                            Izin Khusus
                                        </span>
                                    @endif
                                </div>

                                <!-- Duration Badge -->
                                <span class="px-2 py-0.5 rounded-md bg-white text-teal-800 border border-teal-200/80 font-bold text-[10.5px]">
                                    {{ $item->working_days }} Hari Kerja
                                </span>
                            </div>

                            <!-- Date Range -->
                            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs pt-0.5">
                                <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $item->start_date->format('d M') }} &mdash; {{ $item->end_date->format('d M Y') }}</span>
                            </div>

                            <!-- Alasan -->
                            <div class="pt-2 border-t border-slate-200/70">
                                <span class="text-[10px] font-semibold text-slate-400 block mb-0.5">Alasan:</span>
                                <p class="text-slate-700 text-xs leading-relaxed italic">
                                    "{{ $item->reason }}"
                                </p>
                            </div>

                            <!-- Bukti Surat Lampiran -->
                            @if ($item->attachment_path)
                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                                    <span class="text-[10px] font-semibold text-slate-400">Lampiran:</span>
                                    <a href="{{ route('attendance.leaves.attachment', $item) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold border border-teal-200 text-[11px] transition">
                                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <span>Buka Surat / Bukti &rarr;</span>
                                    </a>
                                </div>
                            @endif

                            <!-- Timestamp -->
                            <div class="pt-1.5 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-400">
                                <span>Diajukan: {{ $item->created_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</span>
                                @if ($item->status === 'approved' && $item->hrdApprover)
                                    <span class="text-emerald-700 font-semibold truncate max-w-[130px]">Oleh: {{ $item->hrdApprover->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 3. Bottom Action Buttons: Full Symmetrical 2-Column Grid (NEVER CUT OFF!) -->
                        @if ($isPending)
                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100">
                                <!-- Tombol Tolak -->
                                <button type="button" 
                                        @click="rejectActionUrl = '{{ route('attendance.leaves.reject', $item) }}'; selectedLeaveId = {{ $item->id }}; rejectModalOpen = true"
                                        class="w-full py-2.5 px-3 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-rose-700 text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Tolak</span>
                                </button>

                                <!-- Tombol Setujui -->
                                <form method="POST" action="{{ route('attendance.leaves.approve-hrd', $item) }}" class="w-full">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti/izin ini? Saldo cuti karyawan akan disesuaikan otomatis.')"
                                            class="w-full py-2.5 px-3 rounded-xl bg-[#155E63] hover:bg-[#0f4448] text-white text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] shadow-sm shadow-teal-900/15 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        <span>Setujui</span>
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $leaves->links() }}
            </div>
        @endif

        <!-- REJECT MODAL PROMPT -->
        <div x-show="rejectModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.stop class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
                
                <!-- Modal Header -->
                <div class="bg-rose-700 text-white px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="!text-white font-bold text-sm sm:text-base leading-tight">Tolak Pengajuan Absen</h3>
                            <p class="text-[11px] text-rose-100/90 mt-0.5">Alasan penolakan akan dikirim ke karyawan</p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="rejectActionUrl" method="POST" class="p-5 space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejected_reason" rows="3" required placeholder="Tuliskan alasan penolakan secara jelas untuk karyawan..."
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-3 text-xs sm:text-sm text-slate-800 focus:bg-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs active:scale-[0.98] cursor-pointer">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
