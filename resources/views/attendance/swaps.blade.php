<x-layouts.app title="Tukar & Backup Jadwal">
    @php
        // Filter incoming requests:
        // 1. Where current user is the target colleague and awaiting their response or supervisor
        $incomingForUser = $mySwaps->filter(fn($s) => $s->target_user_id === $user->id && in_array($s->status, ['pending_target', 'pending_supervisor']));
        // 2. Supervisor queue if user has approval authority
        $supervisorPending = $supervisorQueue->filter(fn($s) => !$incomingForUser->contains('id', $s->id));
        $allIncoming = $incomingForUser->concat($supervisorPending);

        // Filter outgoing requests (created by user, still pending)
        $allOutgoing = $mySwaps->filter(fn($s) => $s->requester_id === $user->id && in_array($s->status, ['pending_target', 'pending_supervisor']));

        // Filter history (completed, rejected, expired)
        $allHistory = $mySwaps->filter(fn($s) => in_array($s->status, ['approved', 'rejected', 'expired']));
    @endphp

    <div x-data="{ 
            activeTab: 'masuk', 
            showAjukanModal: {{ (($errors ?? null) && $errors->any()) ? 'true' : 'false' }} 
         }" 
         class="space-y-6 max-w-5xl mx-auto pb-12">

        <!-- Top Header (Gambar 2) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('attendance.schedules') }}" 
                   title="Kembali ke Jadwal Kerja"
                   class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 hover:bg-slate-50 flex items-center justify-center text-slate-700 shadow-2xs hover:shadow-xs transition-all shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Tukar &amp; Backup Jadwal</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola permintaan pertukaran dan backup shift kamu</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" 
                        @click="showAjukanModal = true" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs sm:text-sm font-semibold transition-all shadow-xs active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Ajukan Tukar / Backup</span>
                </button>
            </div>
        </div>

        <!-- Session Status / Flash Notification -->
        @if (session('status'))
            <div class="rounded-2xl bg-teal-50 border border-teal-200/80 text-teal-800 p-4 flex items-center gap-3 text-xs sm:text-sm shadow-2xs animate-fadeIn">
                <div class="w-8 h-8 rounded-xl bg-teal-100/80 flex items-center justify-center text-[#155E63] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 p-4 text-xs sm:text-sm space-y-1.5 shadow-2xs">
                <div class="font-bold flex items-center gap-2 text-rose-900">
                    <svg class="w-4.5 h-4.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Validasi Pertukaran Jadwal Gagal</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-xs text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tab Bar / Segmented Navigation (Gambar 2) -->
        <div class="rounded-2xl border border-slate-200/80 bg-slate-100/60 p-1.5 flex items-center gap-1 sm:gap-2 shadow-2xs">
            <!-- Tab Masuk -->
            <button type="button" 
                    @click="activeTab = 'masuk'" 
                    :class="activeTab === 'masuk' 
                        ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent font-medium'"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl transition-all text-xs sm:text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <span>Masuk</span>
                @if ($allIncoming->count() > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-[#155E63]">
                        {{ $allIncoming->count() }}
                    </span>
                @endif
            </button>

            <!-- Tab Keluar -->
            <button type="button" 
                    @click="activeTab = 'keluar'" 
                    :class="activeTab === 'keluar' 
                        ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent font-medium'"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl transition-all text-xs sm:text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>Keluar</span>
                @if ($allOutgoing->count() > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                        {{ $allOutgoing->count() }}
                    </span>
                @endif
            </button>

            <!-- Tab Riwayat -->
            <button type="button" 
                    @click="activeTab = 'riwayat'" 
                    :class="activeTab === 'riwayat' 
                        ? 'bg-white text-slate-800 shadow-xs border border-slate-200/70 font-bold' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50 border border-transparent font-medium'"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl transition-all text-xs sm:text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Riwayat</span>
                @if ($allHistory->count() > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                        {{ $allHistory->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Tab 1: MASUK -->
        <div x-show="activeTab === 'masuk'" class="space-y-4">
            @if ($allIncoming->isEmpty())
                <!-- Gambar 2 Empty State -->
                <div class="rounded-3xl border-2 border-dashed border-slate-200/90 bg-white/40 p-12 sm:p-16 flex flex-col items-center justify-center text-center min-h-[340px]">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100/90 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Tidak ada permintaan masuk</h3>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm">Permintaan tukar/backup dari rekan kerja akan muncul di sini</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($allIncoming as $sw)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:border-teal-300 transition-all flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="space-y-2 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-slate-800">
                                        {{ $sw->requester?->name }} &harr; {{ $sw->targetUser?->name }}
                                    </span>
                                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full {{ $sw->is_backup ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                                        {{ $sw->is_backup ? 'Mode Backup' : 'Tukar Shift' }}
                                    </span>
                                    @if ($sw->status === 'pending_target')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                            Menunggu Rekan
                                        </span>
                                    @elseif ($sw->status === 'pending_supervisor')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu Atasan
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs font-mono text-slate-600 bg-slate-50/80 rounded-xl p-2.5 border border-slate-100 flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <div>
                                        <span class="text-slate-400">D1:</span>
                                        <span class="font-semibold text-slate-700">{{ $sw->requester_date->format('d M Y') }}</span>
                                        <span class="text-[#155E63] font-bold">[{{ $sw->reqD1Shift?->code ?? '-' }} &harr; {{ $sw->tarD1Shift?->code ?? '-' }}]</span>
                                    </div>
                                    @if ($sw->requester_date->toDateString() !== $sw->target_date->toDateString())
                                        <div>
                                            <span class="text-slate-400">D2:</span>
                                            <span class="font-semibold text-slate-700">{{ $sw->target_date->format('d M Y') }}</span>
                                            <span class="text-[#155E63] font-bold">[{{ $sw->reqD2Shift?->code ?? '-' }} &harr; {{ $sw->tarD2Shift?->code ?? '-' }}]</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600">
                                    <span class="text-slate-400 font-medium">Alasan:</span> {{ $sw->reason }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                @if ($sw->status === 'pending_target' && $sw->target_user_id === $user->id)
                                    <form method="POST" action="{{ route('attendance.swaps.approve-target', $sw) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-2xs transition-all active:scale-[0.98]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui Rekan</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('attendance.swaps.reject', $sw) }}" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan pertukaran ini?');">
                                        @csrf
                                        <input type="hidden" name="reason" value="Ditolak oleh rekan target">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-all">
                                            <span>Tolak</span>
                                        </button>
                                    </form>
                                @elseif ($sw->status === 'pending_supervisor' && ($user->can('attendance.leave.approve') || $user->can('attendance.schedule.generate')))
                                    <form method="POST" action="{{ route('attendance.swaps.approve-supervisor', $sw) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs font-semibold shadow-2xs transition-all active:scale-[0.98]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui &amp; Tukar Atomik</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('attendance.swaps.reject', $sw) }}" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan tukar ini?');">
                                        @csrf
                                        <input type="hidden" name="reason" value="Ditolak oleh atasan">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-all">
                                            <span>Tolak</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400 italic">Menunggu persetujuan atasan</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tab 2: KELUAR -->
        <div x-show="activeTab === 'keluar'" class="space-y-4">
            @if ($allOutgoing->isEmpty())
                <!-- Gambar 2 Empty State -->
                <div class="rounded-3xl border-2 border-dashed border-slate-200/90 bg-white/40 p-12 sm:p-16 flex flex-col items-center justify-center text-center min-h-[340px]">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100/90 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Tidak ada permintaan keluar</h3>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm">Permintaan tukar/backup yang kamu ajukan akan muncul di sini</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($allOutgoing as $sw)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:border-teal-300 transition-all flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="space-y-2 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-slate-800">
                                        Saya &harr; {{ $sw->targetUser?->name }}
                                    </span>
                                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full {{ $sw->is_backup ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                                        {{ $sw->is_backup ? 'Mode Backup' : 'Tukar Shift' }}
                                    </span>
                                    @if ($sw->status === 'pending_target')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                            Menunggu Rekan Target
                                        </span>
                                    @elseif ($sw->status === 'pending_supervisor')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu Persetujuan Atasan
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs font-mono text-slate-600 bg-slate-50/80 rounded-xl p-2.5 border border-slate-100 flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <div>
                                        <span class="text-slate-400">D1:</span>
                                        <span class="font-semibold text-slate-700">{{ $sw->requester_date->format('d M Y') }}</span>
                                        <span class="text-[#155E63] font-bold">[{{ $sw->reqD1Shift?->code ?? '-' }} &harr; {{ $sw->tarD1Shift?->code ?? '-' }}]</span>
                                    </div>
                                    @if ($sw->requester_date->toDateString() !== $sw->target_date->toDateString())
                                        <div>
                                            <span class="text-slate-400">D2:</span>
                                            <span class="font-semibold text-slate-700">{{ $sw->target_date->format('d M Y') }}</span>
                                            <span class="text-[#155E63] font-bold">[{{ $sw->reqD2Shift?->code ?? '-' }} &harr; {{ $sw->tarD2Shift?->code ?? '-' }}]</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600">
                                    <span class="text-slate-400 font-medium">Alasan:</span> {{ $sw->reason }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <span class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg">
                                    {{ $sw->status === 'pending_target' ? 'Menunggu konfirmasi rekan' : 'Menunggu approval atasan' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tab 3: RIWAYAT -->
        <div x-show="activeTab === 'riwayat'" class="space-y-4">
            @if ($allHistory->isEmpty())
                <!-- Gambar 2 Empty State -->
                <div class="rounded-3xl border-2 border-dashed border-slate-200/90 bg-white/40 p-12 sm:p-16 flex flex-col items-center justify-center text-center min-h-[340px]">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100/90 flex items-center justify-center text-slate-400 mb-4 shadow-2xs">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Tidak ada riwayat tukar/backup</h3>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm">Riwayat pertukaran atau backup shift yang telah selesai akan muncul di sini</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($allHistory as $sw)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:border-slate-300 transition-all flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="space-y-2 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-slate-800">
                                        {{ $sw->requester?->name }} &harr; {{ $sw->targetUser?->name }}
                                    </span>
                                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full {{ $sw->is_backup ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                                        {{ $sw->is_backup ? 'Mode Backup' : 'Tukar Shift' }}
                                    </span>
                                    @if ($sw->status === 'approved')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Disetujui &amp; Ditukar
                                        </span>
                                    @elseif ($sw->status === 'rejected')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @elseif ($sw->status === 'expired')
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                            Kedaluwarsa
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs font-mono text-slate-600 bg-slate-50/80 rounded-xl p-2.5 border border-slate-100 flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <div>
                                        <span class="text-slate-400">D1:</span>
                                        <span class="font-semibold text-slate-700">{{ $sw->requester_date->format('d M Y') }}</span>
                                        <span class="text-slate-500">[{{ $sw->reqD1Shift?->code ?? '-' }} &harr; {{ $sw->tarD1Shift?->code ?? '-' }}]</span>
                                    </div>
                                    @if ($sw->requester_date->toDateString() !== $sw->target_date->toDateString())
                                        <div>
                                            <span class="text-slate-400">D2:</span>
                                            <span class="font-semibold text-slate-700">{{ $sw->target_date->format('d M Y') }}</span>
                                            <span class="text-slate-500">[{{ $sw->reqD2Shift?->code ?? '-' }} &harr; {{ $sw->tarD2Shift?->code ?? '-' }}]</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600">
                                    <span class="text-slate-400 font-medium">Alasan:</span> {{ $sw->reason }}
                                    @if ($sw->rejection_reason)
                                        <span class="text-rose-600 font-semibold block mt-1">Catatan Penolakan: {{ $sw->rejection_reason }}</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $sw->updated_at?->translatedFormat('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- MODAL: AJUKAN TUKAR / BACKUP SHIFT -->
        <div x-show="showAjukanModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="showAjukanModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.stop 
                     class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#155E63] text-white px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base leading-tight">Ajukan Tukar / Backup Jadwal</h3>
                                <p class="text-xs text-teal-100/90 mt-0.5">Divisi: {{ $user->division ?? 'Umum' }}</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="showAjukanModal = false" 
                                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form method="POST" action="{{ route('attendance.swaps.store') }}" class="p-6 space-y-4">
                        @csrf

                        <!-- Rekan Target -->
                        <div>
                            <label for="modal_target_user_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Rekan Target (Satu Divisi) <span class="text-rose-500">*</span>
                            </label>
                            <select id="modal_target_user_id" name="target_user_id" required 
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                                <option value="">— Pilih Rekan Divisi {{ $user->division ?? 'Umum' }} —</option>
                                @foreach ($divisionPeers as $peer)
                                    <option value="{{ $peer->id }}" @selected(old('target_user_id') == $peer->id)>
                                        {{ $peer->name }} ({{ $peer->employee_code }} · {{ $peer->position }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal D1 & D2 -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label for="modal_requester_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Tanggal Pertama (D1) <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="modal_requester_date" name="requester_date"
                                       value="{{ old('requester_date', now('Asia/Jakarta')->addDay()->toDateString()) }}"
                                       required 
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                            </div>
                            <div>
                                <label for="modal_target_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Tanggal Kedua / Silang (D2) <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="modal_target_date" name="target_date"
                                       value="{{ old('target_date', now('Asia/Jakarta')->addDays(2)->toDateString()) }}"
                                       required 
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-mono text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">
                            </div>
                        </div>

                        <!-- Checkbox Mode Backup -->
                        <div class="rounded-xl bg-teal-50/60 border border-teal-200/80 p-3.5">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_backup" value="1" @checked(old('is_backup'))
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#155E63] focus:ring-[#155E63]">
                                <span class="text-xs text-slate-700 leading-relaxed">
                                    <strong class="block font-semibold text-slate-800">Mode Backup Piket &harr; Libur</strong>
                                    Centang bila bertukar jadwal Piket (PK/PA) dengan Libur (L) pada tanggal yang sama (D1 = D2).
                                </span>
                            </label>
                        </div>

                        <!-- Alasan -->
                        <div>
                            <label for="modal_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Alasan Pertukaran Jadwal <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="modal_reason" name="reason" rows="3" required
                                      placeholder="Jelaskan alasan tukar shift atau backup piket..."
                                      class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#155E63] focus:ring-1 focus:ring-[#155E63] outline-none transition">{{ old('reason') }}</textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-slate-100">
                            <button type="button" 
                                    @click="showAjukanModal = false"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl bg-[#155E63] hover:bg-[#114b4f] text-white text-xs sm:text-sm font-semibold transition shadow-xs active:scale-[0.98]">
                                Kirim Permintaan Tukar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
