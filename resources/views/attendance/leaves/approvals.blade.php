<x-layouts.app title="Pengajuan Absen">
    <div class="space-y-6 w-full pb-12" x-data="{ rejectModalOpen: false, selectedLeaveId: null, rejectActionUrl: '' }">
        
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                        <span>Pengajuan Absen (Cuti &amp; Izin)</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            Persetujuan HRD
                        </span>
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Tinjau dan proses persetujuan pengajuan cuti tahunan, sakit, dan izin khusus karyawan.
                    </p>
                </div>
            </div>

            <!-- Right: Profile Info Card -->
            <div class="relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-3.5 py-2 text-white shrink-0 shadow-xs self-start sm:self-auto">
                <div class="relative shrink-0">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" 
                             alt="" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div style="display: none;" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#18C4C4] to-[#155E63] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm shrink-0">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>

                <div class="text-left leading-tight min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs sm:text-sm text-white tracking-tight truncate max-w-[140px] sm:max-w-[200px]">
                            {{ strtoupper($user->name) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] sm:text-[11px] text-teal-200/85">
                        <span class="font-mono text-cyan-200">NIK: {{ $user->employee_code }}</span>
                        @if ($user->division)
                            <span class="truncate">&bull; Divisi: {{ $user->division }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Segmented Tabs Status -->
                <div class="rounded-2xl border border-slate-200/80 bg-slate-100/60 p-1.5 flex flex-wrap items-center gap-1 shadow-2xs w-full lg:w-auto">
                    <!-- Menunggu -->
                    <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                       class="flex-1 sm:flex-none flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'pending' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Menunggu Persetujuan</span>
                        @if ($pendingCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Disetujui -->
                    <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}"
                       class="flex-1 sm:flex-none flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'approved' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Disetujui</span>
                        @if ($approvedCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $approvedCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Ditolak -->
                    <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}"
                       class="flex-1 sm:flex-none flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'rejected' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Ditolak</span>
                        @if ($rejectedCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                {{ $rejectedCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Semua -->
                    <a href="{{ route('attendance.leaves.approvals', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                       class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'all' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                        <span>Semua</span>
                    </a>
                </div>

                <!-- Form Search & Filter -->
                <form method="GET" action="{{ route('attendance.leaves.approvals') }}" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <input type="hidden" name="status" value="{{ $status }}">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 sm:w-56">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari karyawan / NIK..." 
                               class="w-full pl-9 pr-3 py-2 bg-white rounded-xl border border-slate-200/90 text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition shadow-2xs">
                    </div>

                    <!-- Divisi Filter -->
                    <select name="divisi" onchange="this.form.submit()" 
                            class="py-2 pl-3 pr-8 bg-white rounded-xl border border-slate-200/90 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition shadow-2xs">
                        <option value="all">Semua Divisi</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div }}" {{ $divisi === $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>

                    <!-- Jenis Pengajuan Filter -->
                    <select name="type" onchange="this.form.submit()" 
                            class="py-2 pl-3 pr-8 bg-white rounded-xl border border-slate-200/90 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition shadow-2xs">
                        <option value="all">Semua Jenis</option>
                        <option value="annual" {{ $leaveType === 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                        <option value="sick" {{ $leaveType === 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                        <option value="special" {{ $leaveType === 'special' ? 'selected' : '' }}>Izin Khusus</option>
                    </select>

                    <button type="submit" class="px-3.5 py-2 bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold rounded-xl shadow-2xs transition">
                        Filter
                    </button>
                    @if ($search || $divisi !== 'all' || $leaveType !== 'all')
                        <a href="{{ route('attendance.leaves.approvals', ['status' => $status]) }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Alert Notifikasi Session -->
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- List Content Area -->
        @if ($leaves->isEmpty())
            <div class="rounded-3xl border-2 border-dashed border-slate-200/90 bg-white/40 p-12 sm:p-16 flex flex-col items-center justify-center text-center min-h-[320px]">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">
                    @if ($status === 'pending')
                        Tidak ada antrean pengajuan absen
                    @elseif ($status === 'approved')
                        Belum ada riwayat pengajuan disetujui
                    @elseif ($status === 'rejected')
                        Belum ada riwayat pengajuan ditolak
                    @else
                        Tidak ada data pengajuan yang ditemukan
                    @endif
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 max-w-sm">
                    @if ($status === 'pending')
                        Semua pengajuan cuti, izin, dan sakit dari karyawan telah selesai ditinjau.
                    @else
                        Riwayat pengajuan absen karyawan akan tercatat di sini.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($leaves as $item)
                    @php
                        $emp = $item->user;
                        $isPending = in_array($item->status, ['pending_hrd', 'pending_supervisor']);
                    @endphp
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs hover:border-teal-200 transition-all space-y-4">
                        
                        <!-- Card Header: Profile, Badges, & Actions -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                            
                            <!-- Profile Info -->
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-teal-50 border border-teal-100 text-[#155E63] font-black flex items-center justify-center text-sm shrink-0 overflow-hidden shadow-2xs">
                                    @if ($emp?->avatar_url)
                                        <img src="{{ $emp->avatar_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ strtoupper(substr($emp?->name ?? 'U', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-heading font-bold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                                        <span>{{ $emp?->name }}</span>
                                        <span class="text-xs font-mono font-medium text-slate-400">({{ $emp?->employee_code }})</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1 font-semibold text-teal-700">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            Divisi: {{ $emp?->division ?? '-' }}
                                        </span>
                                        <span>&bull;</span>
                                        <span class="font-mono text-slate-400">Diajukan: {{ $item->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Badges Status & Jenis -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Badge Jenis Pengajuan -->
                                @if ($item->leave_type === 'annual')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                        Cuti Tahunan
                                    </span>
                                @elseif ($item->leave_type === 'sick')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Cuti Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Izin Khusus
                                    </span>
                                @endif

                                <!-- Badge Status -->
                                @if ($isPending)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Menunggu Persetujuan HRD
                                    </span>
                                @elseif ($item->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Disetujui HRD
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body: Date Range, Reason, & Attachment -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-200/60">
                            <!-- Tanggal & Durasi -->
                            <div>
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Periode &amp; Durasi
                                </span>
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $item->start_date->format('d M Y') }} &mdash; {{ $item->end_date->format('d M Y') }}</span>
                                </div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-teal-100/80 text-teal-800 font-mono text-[11px] font-bold">
                                    {{ $item->working_days }} Hari Kerja
                                </span>
                            </div>

                            <!-- Alasan Pengajuan -->
                            <div class="md:col-span-1">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Alasan Pengajuan
                                </span>
                                <p class="text-xs text-slate-700 leading-relaxed italic">
                                    "{{ $item->reason }}"
                                </p>
                            </div>

                            <!-- Lampiran Dokumen / Surat Dokter -->
                            <div>
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Lampiran / Bukti
                                </span>
                                @if ($item->attachment_path)
                                    <a href="{{ route('attendance.leaves.attachment', $item) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-teal-300 text-teal-700 hover:bg-teal-50 text-xs font-bold shadow-2xs hover:shadow-xs transition">
                                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span>Buka Surat / Bukti</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada lampiran</span>
                                @endif
                            </div>
                        </div>

                        <!-- Footer: Log & Action Buttons -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                            
                            <!-- Tracking Status / Approval Log -->
                            <div class="text-xs text-slate-500">
                                @if ($item->status === 'approved')
                                    <span class="text-emerald-700 font-semibold flex items-center gap-1">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Disetujui oleh: <strong>{{ $item->hrdApprover?->name ?? 'HRD' }}</strong> pada {{ $item->hrd_approved_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                    </span>
                                @elseif ($item->status === 'rejected')
                                    <div class="space-y-0.5">
                                        <span class="text-rose-700 font-semibold flex items-center gap-1">
                                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Ditolak oleh: <strong>{{ $item->rejectedBy?->name ?? 'HRD' }}</strong>
                                        </span>
                                        @if ($item->rejected_reason)
                                            <p class="text-rose-600 text-[11px] italic pl-5">Alasan: "{{ $item->rejected_reason }}"</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-amber-700 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu tindak lanjut dari HRD
                                    </span>
                                @endif
                            </div>

                            <!-- Tombol Aksi (Hanya muncul jika Pending) -->
                            @if ($isPending)
                                <div class="flex items-center gap-2">
                                    <!-- Tombol Tolak -->
                                    <button type="button" 
                                            @click="rejectActionUrl = '{{ route('attendance.leaves.reject', $item) }}'; selectedLeaveId = {{ $item->id }}; rejectModalOpen = true"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition shadow-2xs hover:shadow-xs active:scale-[0.98] cursor-pointer">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tolak</span>
                                    </button>

                                    <!-- Tombol Setujui -->
                                    <form method="POST" action="{{ route('attendance.leaves.approve-hrd', $item) }}">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Apakah Anda yakin ingin menyetujui pengajuan cuti/izin ini? Saldo cuti akan disesuaikan otomatis.')"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition shadow-2xs hover:shadow-xs active:scale-[0.98] cursor-pointer">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui (Approve)</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="pt-4">
                    {{ $leaves->links() }}
                </div>
            </div>
        @endif

        <!-- REJECT MODAL PROMPT -->
        <div x-show="rejectModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.stop class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
                
                <!-- Modal Header -->
                <div class="bg-rose-700 text-white px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="!text-white font-bold text-base leading-tight">Tolak Pengajuan Absen</h3>
                            <p class="text-xs text-rose-100/90 mt-0.5">Berikan alasan penolakan untuk karyawan</p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="rejectActionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejected_reason" rows="3" required placeholder="Tuliskan alasan penolakan secara jelas untuk karyawan..."
                                  class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs sm:text-sm text-slate-800 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold transition shadow-xs active:scale-[0.98] cursor-pointer">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
