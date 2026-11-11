<x-layouts.app title="Pengajuan Absen">
    <div class="space-y-6 w-full pb-12" x-data="{ rejectModalOpen: false, selectedCorrectionId: null, rejectActionUrl: '' }">
        
        <!-- Top Page Header Banner (Sculpted Metallic Ocean Teal matching Dashboard) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Glowing Icon + Page Name -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-cyan-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-cyan-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2 leading-snug">
                        <span>Pengajuan Absen</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            Khusus Atasan
                        </span>
                    </h1>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Kelola dan tinjau pengajuan koreksi kehadiran karyawan dari divisi Anda.
                    </p>
                </div>
            </div>

            <!-- Right: Profile Info Card (Geser ke Kanan, tanpa awan putih) -->
            <div class="relative z-10 flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/15 rounded-xl px-3.5 py-2 text-white shrink-0 shadow-xs self-start sm:self-auto">
                <div class="relative shrink-0">
                    @if ($user->foto)
                        <img src="/storage/{{ $user->foto }}" 
                             alt="{{ $user->name }}" 
                             class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-cyan-400/40 shadow-sm" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=155E63&color=fff&size=128';">
                    @else
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-[#155E63] to-[#0c4349] border-2 border-cyan-400/40 text-white flex items-center justify-center text-xs sm:text-sm font-bold shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
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
                            <span class="truncate">· Divisi: {{ $user->division }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Toolbar: Back Button & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('attendance.history') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/80 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs hover:shadow-xs transition-all w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Riwayat Absen</span>
            </a>

            <!-- Segmented Tab Navigation -->
            <div class="rounded-2xl border border-slate-200/80 bg-slate-100/60 p-1.5 flex items-center gap-1 sm:gap-2 shadow-2xs w-full sm:w-auto">
                <!-- Menunggu -->
                <a href="{{ route('attendance.corrections.approvals', ['status' => 'pending']) }}"
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
                <a href="{{ route('attendance.corrections.approvals', ['status' => 'approved']) }}"
                   class="flex-1 sm:flex-none flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'approved' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Disetujui</span>
                </a>

                <!-- Ditolak -->
                <a href="{{ route('attendance.corrections.approvals', ['status' => 'rejected']) }}"
                   class="flex-1 sm:flex-none flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl transition-all text-xs font-semibold {{ $status === 'rejected' ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Ditolak</span>
                </a>
            </div>
        </div>

        <!-- Content Area -->
        @if ($corrections->isEmpty())
            <div class="rounded-3xl border-2 border-dashed border-slate-200/90 bg-white/40 p-12 sm:p-16 flex flex-col items-center justify-center text-center min-h-[320px]">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">
                    @if ($status === 'pending')
                        Tidak ada antrean koreksi absen
                    @elseif ($status === 'approved')
                        Belum ada riwayat koreksi disetujui
                    @else
                        Belum ada riwayat koreksi ditolak
                    @endif
                </h3>
                <p class="text-xs sm:text-sm text-slate-400 max-w-sm">
                    @if ($status === 'pending')
                        Semua pengajuan koreksi kehadiran dari karyawan telah diproses.
                    @else
                        Daftar riwayat verifikasi akan muncul di sini.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($corrections as $c)
                    @php
                        $parsedDate = \Carbon\Carbon::parse($c->date);
                        $att = $c->attendance;
                    @endphp
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs hover:border-teal-200 transition-all space-y-4">
                        <!-- Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 text-[#155E63] font-bold flex items-center justify-center text-sm shrink-0 border border-teal-100">
                                    {{ strtoupper(substr($c->user?->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                        <span>{{ $c->user?->name }}</span>
                                        <span class="text-xs font-mono font-normal text-slate-400">({{ $c->user?->employee_code }})</span>
                                    </div>
                                    <span class="text-xs text-slate-500">
                                        Divisi: {{ $c->user?->division ?? 'Umum' }} · Diajukan pada: {{ $c->created_at?->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-slate-100 text-slate-700">
                                    {{ $parsedDate->translatedFormat('l, d F Y') }}
                                </span>
                                @if ($c->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif ($c->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Atasan
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Comparison Body -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <!-- Kondisi Tercatat -->
                            <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100 space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">KONDISI TERCATAT SISTEM</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-slate-400 block text-[11px]">Jam Masuk:</span>
                                        <span class="font-bold font-mono text-slate-700">
                                            {{ $att?->clock_in_at ? \Carbon\Carbon::parse($att->clock_in_at)->format('H:i:s') : '-' }}
                                        </span>
                                        @if (($att?->late_minutes ?? 0) > 0)
                                            <span class="text-[10px] text-amber-600 block">(Telat {{ $att->late_minutes }}m)</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[11px]">Jam Pulang:</span>
                                        <span class="font-bold font-mono text-slate-700">
                                            {{ $att?->clock_out_at ? \Carbon\Carbon::parse($att->clock_out_at)->format('H:i:s') : '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="pt-1 text-[11px] text-slate-500">
                                    Status Asal: <strong class="text-slate-700">{{ strtoupper($att?->status ?? 'TIDAK HADIR') }}</strong> · Shift: {{ $att?->shift?->name ?? 'Normal' }}
                                </div>
                            </div>

                            <!-- Koreksi yang Diajukan -->
                            <div class="rounded-xl bg-teal-50/60 p-3.5 border border-teal-200/80 space-y-2">
                                <span class="text-[10px] font-bold text-[#155E63] uppercase tracking-wider block">KOREKSI YANG DIAJUKAN KARYAWAN</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-teal-700/80 block text-[11px]">Jam Masuk Usulan:</span>
                                        <span class="font-bold font-mono text-[#155E63] text-sm">
                                            {{ substr($c->requested_clock_in_time ?? '08:00', 0, 5) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-teal-700/80 block text-[11px]">Jam Pulang Usulan:</span>
                                        <span class="font-bold font-mono text-[#155E63] text-sm">
                                            {{ substr($c->requested_clock_out_time ?? '17:00', 0, 5) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="pt-1 text-[11px] text-teal-900">
                                    Status Usulan: <strong class="capitalize">{{ $c->requested_status === 'present' ? 'Hadir Tepat Waktu' : $c->requested_status }}</strong> (Tipe: {{ ucfirst($c->requested_attendance_type) }})
                                </div>
                            </div>
                        </div>

                        <!-- Alasan & Lampiran -->
                        <div class="text-xs space-y-2">
                            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                                <span class="font-semibold text-slate-700">Alasan Koreksi:</span>
                                <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $c->reason }}</p>
                            </div>

                            @if ($c->attachment_path)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <a href="{{ asset('storage/' . $c->attachment_path) }}" target="_blank" class="text-xs text-[#155E63] font-semibold hover:underline flex items-center gap-1">
                                        <span>Lihat Bukti Lampiran</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @endif

                            @if ($c->status === 'rejected' && $c->rejected_reason)
                                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-800">
                                    <span class="font-bold">Catatan Penolakan:</span>
                                    <p class="mt-0.5">{{ $c->rejected_reason }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        @if (in_array($c->status, ['pending_supervisor', 'pending_hrd']))
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                <button type="button" 
                                        @click="rejectActionUrl = '{{ route('attendance.corrections.reject', $c) }}'; rejectModalOpen = true;"
                                        class="px-4 py-2 rounded-xl border border-rose-200 text-rose-700 bg-rose-50/50 hover:bg-rose-100 text-xs font-semibold transition active:scale-[0.98]">
                                    Tolak Pengajuan
                                </button>

                                <form method="POST" action="{{ route('attendance.corrections.approve-supervisor', $c) }}" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menyetujui koreksi absensi ini? Data absensi karyawan akan langsung diperbarui sesuai waktu yang diajukan.');">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold transition shadow-xs active:scale-[0.98]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Setujui &amp; Perbarui Absen</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Status: <strong class="capitalize">{{ $c->status }}</strong></span>
                                <span>
                                    @if ($c->status === 'approved')
                                        Disetujui oleh {{ $c->supervisorApprover?->name ?? 'Atasan' }}
                                    @elseif ($c->status === 'rejected')
                                        Ditolak oleh {{ $c->rejectedBy?->name ?? 'Atasan' }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="pt-2">
                    {{ $corrections->links() }}
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
                            <h3 class="!text-white font-bold text-base leading-tight">Tolak Pengajuan Koreksi</h3>
                            <p class="text-xs text-rose-100/90 mt-0.5">Berikan alasan penolakan secara jelas untuk karyawan</p>
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
                        <textarea name="reason" rows="3" required placeholder="Tuliskan alasan penolakan secara jelas untuk karyawan..."
                                  class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs sm:text-sm text-slate-800 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold transition shadow-xs active:scale-[0.98]">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>

