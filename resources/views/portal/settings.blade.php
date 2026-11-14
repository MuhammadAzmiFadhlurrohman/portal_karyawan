<x-layouts.app title="Pengaturan Sistem">
    <div class="space-y-6 w-full pb-16" 
         x-data="{ 
            activeGroup: 'all', 
            search: '', 
            showAddModal: false 
         }">

        <!-- 1. Top Page Header Banner (Sculpted Metallic Ocean Teal) -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/30 shadow-[0_4px_20px_rgba(2,43,58,0.35)] p-4 sm:py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none"
             style="background: linear-gradient(90deg, #022836 0%, #053b47 25%, #0a525f 60%, #0e6774 85%, #084c57 100%);">
            
            <!-- Gloss Highlights (Top & Bottom Rims) -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/70 to-transparent pointer-events-none"></div>
            <div class="absolute inset-x-4 bottom-0 h-[1px] bg-gradient-to-r from-transparent via-[#2dd4bf]/35 to-transparent pointer-events-none"></div>

            <!-- Left: Back Button + Glowing Gear Icon + Title -->
            <div class="relative z-10 flex items-center gap-3.5 min-w-0">
                <a href="{{ route('portal.hub') }}" 
                   class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition"
                   title="Kembali ke Portal Hub">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-teal-300 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-teal-300 drop-shadow-[0_0_8px_rgba(45,212,191,0.5)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-heading text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
                            Pengaturan Sistem
                        </h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-400/20 text-teal-300 border border-teal-400/30">
                            Single Source of Truth
                        </span>
                    </div>
                    <p class="text-xs sm:text-[13px] text-teal-100/80 mt-0.5 leading-snug truncate">
                        Parameter operasional, kuota, toleransi absensi, denda, dan kebijakan sistem berjejak audit
                    </p>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="relative z-10 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('portal.settings.reset') }}" onsubmit="return confirm('Pulihkan seluruh parameter standar sistem? Nilai yang belum ada akan dibuatkan otomatis.');">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-teal-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Pulihkan Default</span>
                    </button>
                </form>

                <button type="button" 
                        @click="showAddModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-teal-500 to-teal-400 hover:from-teal-400 hover:to-teal-300 text-slate-950 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Tambah Parameter</span>
                </button>
            </div>
        </div>

        <!-- 2. Status & Error Notifications -->
        @if (session('status'))
            <div class="rounded-2xl bg-teal-50 border border-teal-200 p-4 flex items-center justify-between gap-3 text-xs text-teal-900 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800 shadow-2xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-rose-900">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>Terjadi kesalahan pengisian parameter:</span>
                </div>
                <ul class="list-disc pl-6 space-y-0.5 text-rose-700">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $groupMetadata = [
                'attendance' => ['label' => 'Absensi & Denda', 'color' => 'bg-teal-50 text-teal-700 border-teal-200', 'badge' => 'bg-teal-600 text-white'],
                'geofence'   => ['label' => 'Lokasi & GPS', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'badge' => 'bg-emerald-600 text-white'],
                'biometric'  => ['label' => 'Wajah & Biometrik', 'color' => 'bg-purple-50 text-purple-700 border-purple-200', 'badge' => 'bg-purple-600 text-white'],
                'leave'      => ['label' => 'Cuti & Izin', 'color' => 'bg-amber-50 text-amber-700 border-amber-200', 'badge' => 'bg-amber-600 text-white'],
                'ticketing'  => ['label' => 'Ticketing & SLA', 'color' => 'bg-blue-50 text-blue-700 border-blue-200', 'badge' => 'bg-blue-600 text-white'],
                'gomsn'      => ['label' => 'GoMSN / Keuangan', 'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'badge' => 'bg-indigo-600 text-white'],
                'security'   => ['label' => 'Keamanan & Sesi', 'color' => 'bg-rose-50 text-rose-700 border-rose-200', 'badge' => 'bg-rose-600 text-white'],
            ];

            $groups = $currentSettings->groupBy('group')->map->count();
        @endphp

        <!-- 3. Search & Category Filters -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari nama parameter, deskripsi, atau key..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" 
                            x-show="search.length > 0" 
                            @click="search = ''" 
                            class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                        &times;
                    </button>
                </div>

                <div class="text-[11px] text-slate-500 shrink-0 font-medium self-end sm:self-center">
                    Total: <strong class="text-slate-800 font-mono">{{ $currentSettings->count() }}</strong> parameter terkonfigurasi
                </div>
            </div>

            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs select-none">
                <button type="button" 
                        @click="activeGroup = 'all'"
                        :class="activeGroup === 'all' 
                            ? 'bg-[#022836] text-white shadow-xs' 
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70 border border-transparent'"
                        class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                          :class="activeGroup === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $currentSettings->count() }}
                    </span>
                </button>

                @foreach ($groups as $grpKey => $grpCount)
                    @php
                        $meta = $groupMetadata[$grpKey] ?? [
                            'label' => ucfirst($grpKey),
                            'color' => 'bg-slate-50 text-slate-700 border-slate-200',
                            'badge' => 'bg-slate-600 text-white',
                        ];
                    @endphp
                    <button type="button" 
                            @click="activeGroup = '{{ $grpKey }}'"
                            :class="activeGroup === '{{ $grpKey }}' 
                                ? 'bg-[#022836] text-white shadow-xs' 
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70 border border-transparent'"
                            class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0">
                        <span>{{ $meta['label'] }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                              :class="activeGroup === '{{ $grpKey }}' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ $grpCount }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 4. Settings List / Cards Grid -->
        <div class="space-y-4">
            @forelse ($currentSettings as $setting)
                @php
                    $meta = $groupMetadata[$setting->group] ?? [
                        'label' => ucfirst($setting->group),
                        'color' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'badge' => 'bg-slate-600 text-white',
                    ];
                    $revisions = $historyByKey->get($setting->key, collect());
                    $normalizedVal = strtolower((string)$setting->value);
                    $isBooleanType = $setting->type === 'boolean' || in_array($normalizedVal, ['true', 'false', '1', '0']);
                @endphp

                <div class="rounded-2xl bg-white border border-slate-200/90 hover:border-teal-400/80 p-5 shadow-xs hover:shadow-md transition-all duration-200"
                     x-data="{ showRevisions: false }"
                     x-show="(activeGroup === 'all' || activeGroup === '{{ $setting->group }}') && ('{{ strtolower($setting->label . ' ' . $setting->key . ' ' . $setting->group . ' ' . $setting->description) }}'.includes(search.toLowerCase().trim()))"
                     x-transition>
                    
                    <form method="POST" action="{{ route('portal.settings.update') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="key" value="{{ $setting->key }}">

                        <!-- Left Info (Col 5) -->
                        <div class="lg:col-span-5 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $meta['color'] }}">
                                    {{ $meta['label'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $setting->key }}
                                </span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono uppercase bg-slate-50 text-slate-400 border border-slate-200">
                                    {{ $setting->type }}
                                </span>
                            </div>

                            <label class="block font-heading text-sm font-bold text-slate-800 leading-snug">
                                {{ $setting->label }}
                            </label>
                            
                            <p class="text-xs text-slate-500 leading-relaxed">
                                {{ $setting->description ?: 'Parameter konfigurasi operasional sistem.' }}
                            </p>
                        </div>

                        <!-- Value Input (Col 2) -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Nilai Parameter
                            </label>

                            @if ($setting->type === 'boolean')
                                <select name="value" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                    <option value="true" {{ in_array($normalizedVal, ['true', '1', 'yes', 'on'], true) ? 'selected' : '' }}>Aktif (True)</option>
                                    <option value="false" {{ in_array($normalizedVal, ['false', '0', 'no', 'off'], true) ? 'selected' : '' }}>Nonaktif (False)</option>
                                </select>
                            @elseif ($setting->type === 'integer' || $setting->type === 'decimal' || $setting->type === 'float')
                                <div class="relative">
                                    @if (str_contains($setting->key, 'fee') || (str_contains($setting->key, 'threshold') && str_contains($setting->key, 'gomsn')))
                                        <span class="absolute left-3 top-2 text-xs font-bold text-slate-400 select-none">Rp</span>
                                        <input type="number" 
                                               step="{{ $setting->type === 'integer' ? '1' : 'any' }}" 
                                               name="value" 
                                               required 
                                               value="{{ $setting->value }}" 
                                               class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-2 text-xs font-mono font-bold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                    @else
                                        <input type="number" 
                                               step="{{ $setting->type === 'integer' ? '1' : 'any' }}" 
                                               name="value" 
                                               required 
                                               value="{{ $setting->value }}" 
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                    @endif
                                </div>
                            @else
                                <input type="text" 
                                       name="value" 
                                       required 
                                       value="{{ $setting->value }}" 
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            @endif
                        </div>

                        <!-- Effective Date (Col 2) -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Berlaku Mulai
                            </label>
                            <input type="datetime-local" 
                                   name="effective_from" 
                                   required 
                                   value="{{ now()->format('Y-m-d\TH:i') }}" 
                                   class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-mono text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <!-- Change Reason (Col 2) -->
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Alasan Perubahan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="change_reason" 
                                   required 
                                   placeholder="Mis: Penyesuaian aturan Q4" 
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none placeholder:text-slate-400">
                        </div>

                        <!-- Submit Button (Col 1) -->
                        <div class="lg:col-span-1">
                            <button type="submit" 
                                    class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-teal-700 to-teal-600 hover:from-teal-800 hover:to-teal-700 text-white font-bold text-xs shadow-xs hover:shadow-md transition">
                                Simpan
                            </button>
                        </div>
                    </form>

                    <!-- Audit Trail / Revision History Accordion -->
                    @if ($revisions->count() > 1)
                        <div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <button type="button" 
                                        @click="showRevisions = !showRevisions"
                                        class="inline-flex items-center gap-1.5 text-[11px] font-bold text-teal-700 hover:text-teal-900 transition">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="showRevisions ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span>Riwayat Revisi ({{ $revisions->count() }} versi terdahulu)</span>
                                </button>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    Update terakhir: {{ $setting->effective_from?->format('d M Y H:i') ?? '-' }}
                                </span>
                            </div>

                            <div x-show="showRevisions" x-collapse class="mt-2 space-y-1.5">
                                <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200/70 space-y-2">
                                    @foreach ($revisions as $rev)
                                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] border-b border-slate-200/60 pb-1.5 last:border-b-0 last:pb-0">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono font-bold text-slate-800">
                                                    {{ $rev->value }}
                                                </span>
                                                <span class="text-slate-500">
                                                    Alasan: <strong class="text-slate-700 font-normal">"{{ $rev->change_reason }}"</strong>
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                Berlaku {{ $rev->effective_from?->format('d M Y H:i') }}
                                                @if($rev->updater)
                                                    · oleh {{ $rev->updater->name }}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <!-- Empty State -->
                <div class="rounded-2xl bg-white border border-slate-200 p-12 text-center shadow-xs">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Parameter Belum Terdaftar</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Database parameter konfigurasi saat ini masih kosong. Klik tombol di bawah untuk memulihkan seluruh 21 parameter standar aplikasi secara otomatis.
                    </p>
                    <div class="mt-5">
                        <form method="POST" action="{{ route('portal.settings.reset') }}">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-700 to-teal-600 hover:from-teal-800 hover:to-teal-700 text-white font-bold text-xs shadow-md transition">
                                <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Inisialisasi Parameter Sistem Standar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- 5. Modal Tambah Parameter Baru -->
        <div x-show="showAddModal" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="showAddModal = false"></div>

            <!-- Modal Card -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4"
                     @click.stop>
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-heading text-base font-bold text-slate-800 leading-tight">Tambah Parameter Konfigurasi</h3>
                                <p class="text-xs text-slate-500">Daftarkan parameter baru ke database pengaturan sistem</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('portal.settings.create') }}" class="space-y-3.5">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Key Parameter <span class="text-rose-500">*</span></label>
                                <input type="text" name="key" required placeholder="mis: finance.ppn_rate" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori / Group <span class="text-rose-500">*</span></label>
                                <select name="group" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                    <option value="attendance">Absensi & Denda (attendance)</option>
                                    <option value="geofence">Lokasi & GPS (geofence)</option>
                                    <option value="biometric">Wajah & Biometrik (biometric)</option>
                                    <option value="leave">Cuti & Izin (leave)</option>
                                    <option value="ticketing">Ticketing & SLA (ticketing)</option>
                                    <option value="gomsn">GoMSN & Finansial (gomsn)</option>
                                    <option value="security">Keamanan & Sesi (security)</option>
                                    <option value="general">Umum (general)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Label / Judul Parameter <span class="text-rose-500">*</span></label>
                            <input type="text" name="label" required placeholder="mis: Tarif PPN Default Pengadaan" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Data <span class="text-rose-500">*</span></label>
                                <select name="type" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                    <option value="integer">Integer (Angka Bulat)</option>
                                    <option value="decimal">Decimal (Uang / Desimal)</option>
                                    <option value="boolean">Boolean (True / False)</option>
                                    <option value="string">String (Teks)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Parameter <span class="text-rose-500">*</span></label>
                                <input type="text" name="value" required placeholder="mis: 11 atau true" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Penjelasan</label>
                            <textarea name="description" rows="2" placeholder="Jelaskan kegunaan parameter ini untuk operasional..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Berlaku Mulai <span class="text-rose-500">*</span></label>
                                <input type="datetime-local" name="effective_from" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-mono text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penambahan <span class="text-rose-500">*</span></label>
                                <input type="text" name="change_reason" required placeholder="mis: Kebutuhan operasional baru" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-700 to-teal-600 hover:from-teal-800 hover:to-teal-700 text-white font-bold text-xs shadow-xs transition">
                                Simpan Parameter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
