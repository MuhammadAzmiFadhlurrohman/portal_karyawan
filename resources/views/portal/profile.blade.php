<x-layouts.app title="Profil Saya - Portal MSN">
    @php
        $roleName = $user->getRoleNames()->first() ? strtoupper(str_replace('_', ' ', $user->getRoleNames()->first())) : 'STAFF';
        $hasFaceRegistered = $user->faceTemplates && $user->faceTemplates->where('is_active', true)->isNotEmpty();
        $joinDateFormatted = $user->join_date ? \Carbon\Carbon::parse($user->join_date)->translatedFormat('d F Y') : 'Belum diatur';
        $tenureText = $user->join_date ? \Carbon\Carbon::parse($user->join_date)->diffForHumans(null, true) : '-';
    @endphp

    <div class="max-w-6xl mx-auto space-y-6 pb-8"
         x-data="{
            photoPreview: null,
            photoName: '',
            bankName: @js(old('bank', $user->bank ?? '')),
            accountNumber: @js(old('no_rekening', $user->no_rekening ?? '')),
            accountHolder: @js(old('atas_nama', $user->atas_nama ?: $user->name)),
            phoneVal: @js(old('phone', $user->phone ?? '')),
            showCurrentPass: false,
            showNewPass: false,
            showConfirmPass: false,
            handlePhotoChange(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.photoName = file.name;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
         }">

        <!-- Validation Errors Alert -->
        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 shadow-2xs">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-rose-800 space-y-1">
                        <p class="font-bold text-rose-900">Terdapat kesalahan pada data yang dimasukkan:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- HERO PROFILE CARD -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_8px_30px_rgb(15,23,42,0.05)] overflow-hidden">
            <!-- Top Decorative Banner -->
            <div class="relative h-32 sm:h-40 w-full overflow-hidden"
                 style="background: linear-gradient(135deg, #093c40 0%, #155E63 48%, #0f172a 100%) !important;">
                <!-- Decorative Glow & Pattern -->
                <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-teal-400/15 blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-20 left-1/4 w-72 h-72 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>
                <div class="absolute inset-0 opacity-15 pointer-events-none"
                     style="background-image: radial-gradient(rgba(255,255,255,0.45) 1px, transparent 1px); background-size: 20px 20px;"></div>

                <!-- Top Bar inside Banner: Status Pills & Admin Action -->
                <div class="relative z-10 px-4 sm:px-7 pt-4 sm:pt-5 flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white/12 text-white backdrop-blur-md border border-white/20">
                            <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                            {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                        </span>

                        @if ($hasFaceRegistered)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-100 backdrop-blur-md border border-emerald-400/30">
                                <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Biometrik Wajah Aktif
                            </span>
                        @else
                            <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-500/25 hover:bg-amber-500/35 text-amber-100 backdrop-blur-md border border-amber-300/30 transition">
                                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Belum Daftar Wajah
                            </a>
                        @endif
                    </div>

                    @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                        <a href="{{ route('portal.sessions') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white/15 hover:bg-white/25 text-white backdrop-blur-md border border-white/25 shadow-xs transition">
                            <svg class="w-4 h-4 text-teal-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Sesi &amp; Perangkat</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Identity Body (Clean Separation from Banner so Name Never Collides) -->
            <div class="px-5 sm:px-7 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <!-- Avatar + Name Block -->
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-5">
                        <!-- Overlapping Avatar Container -->
                        <div class="relative -mt-12 sm:-mt-14 w-24 h-24 sm:w-28 sm:h-28 shrink-0 group">
                            <!-- Live Photo Preview when selecting new photo -->
                            <template x-if="photoPreview">
                                <img :src="photoPreview"
                                     alt="Preview Foto Baru"
                                     class="w-full h-full rounded-2xl object-cover shadow-lg ring-4 ring-white bg-white">
                            </template>

                            <template x-if="!photoPreview">
                                <div class="w-full h-full">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}"
                                             alt="{{ $user->name }}"
                                             onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                             class="w-full h-full rounded-2xl object-cover shadow-lg ring-4 ring-white bg-white">
                                        <div style="display: none;"
                                             class="w-full h-full rounded-2xl bg-gradient-to-tr from-[#155E63] to-[#0f172a] text-white items-center justify-center font-bold text-3xl shadow-lg ring-4 ring-white">
                                            {{ $user->initials }}
                                        </div>
                                    @else
                                        <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-[#155E63] to-[#0f172a] text-white flex items-center justify-center font-bold text-3xl shadow-lg ring-4 ring-white">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                </div>
                            </template>

                            <!-- Camera Trigger Button to Change Avatar -->
                            <label for="profile-foto-input"
                                   title="Ubah Foto Profil"
                                   class="absolute -bottom-1.5 -right-1.5 w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[#155E63] hover:bg-[#0e4549] text-white flex items-center justify-center shadow-md ring-2 ring-white cursor-pointer transition transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </label>
                        </div>

                        <!-- Name, Role & Subtitle (Strictly inside White Area) -->
                        <div class="pt-1 sm:pt-3 sm:pb-0.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                    {{ $user->name }}
                                </h1>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-[#155E63] border border-teal-200/80">
                                    <svg class="w-3 h-3 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $roleName }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-mono font-semibold text-slate-700">{{ $user->employee_code }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ $user->division ?: 'Operasional' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ $user->position ?: 'Staff' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Quick Action / Photo Selected Indicator -->
                    <div class="flex items-center gap-2 self-start sm:self-end">
                        <template x-if="photoPreview">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-50 border border-teal-200 text-xs text-teal-800">
                                <span class="font-semibold truncate max-w-[160px]" x-text="'Foto baru: ' + photoName"></span>
                                <button type="button"
                                        @click="photoPreview = null; photoName = ''; document.getElementById('profile-foto-input').value = '';"
                                        class="text-rose-600 hover:text-rose-700 font-bold cursor-pointer">
                                    Batal
                                </button>
                            </div>
                        </template>
                        <button type="button"
                                @click="document.getElementById('profile-foto-input').click()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200/80 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Ganti Foto</span>
                        </button>
                    </div>
                </div>

                <!-- 4 Mini Quick Highlight Pills (Responsive Bento Strip) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/60 p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-[#155E63] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">ID Karyawan</span>
                            <span class="text-xs sm:text-sm font-bold font-mono text-slate-800 truncate block">{{ $user->employee_code }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/60 p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Divisi &amp; Posisi</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 truncate block">{{ $user->division ?: 'Umum' }} &middot; {{ $user->position ?: 'Staff' }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/60 p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Atasan Langsung</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 truncate block">{{ $user->supervisor?->name ?? 'Direksi / Mandiri' }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/60 p-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 block">Mulai Bergabung</span>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 truncate block">{{ $joinDateFormatted }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN 2-COLUMN CONTENT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- KOLOM KIRI (lg:col-span-5): Data Kepegawaian & Preview Rekening -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Card 1: Data Kepegawaian (Read-Only) -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_8px_30px_rgb(15,23,42,0.04)] p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-teal-50 text-[#155E63] flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">Data Kepegawaian</h2>
                                <p class="text-[11px] text-slate-500">Informasi resmi pada sistem HRD PT MSN</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider shrink-0">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            HRD Verified
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <!-- Row Item -->
                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                <span class="text-slate-500">Kode Karyawan</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200/80">{{ $user->employee_code }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                                </svg>
                                <span class="text-slate-500">NIK Kependudukan</span>
                            </div>
                            <span class="font-mono font-semibold text-slate-800 truncate">{{ $user->nik ?: '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                                <span class="text-slate-500">Username Login</span>
                            </div>
                            <span class="font-mono font-semibold text-[#155E63] truncate">{{ '@' . $user->username }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-slate-500">Email Resmi</span>
                            </div>
                            <span class="font-semibold text-slate-800 truncate max-w-[200px] sm:max-w-[240px]" title="{{ $user->email }}">{{ $user->email }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                </svg>
                                <span class="text-slate-500">Divisi / Departemen</span>
                            </div>
                            <span class="font-semibold text-slate-800">{{ $user->division ?: '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-slate-500">Jabatan / Posisi</span>
                            </div>
                            <span class="font-semibold text-slate-800">{{ $user->position ?: '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-slate-500">Atasan Langsung</span>
                            </div>
                            <span class="font-semibold text-slate-800 text-right">{{ $user->supervisor?->name ?? 'Pengajuan Mandiri / Direksi' }}</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 leading-relaxed pt-1">
                        Jika terdapat perubahan pada data kepegawaian di atas, silakan hubungi tim <strong class="text-slate-600">HRD / Administrator</strong>.
                    </p>
                </div>

                <!-- Card 2: Interactive Bank Account Card Preview -->
                <div class="relative rounded-3xl p-5 sm:p-6 text-white overflow-hidden shadow-lg"
                     style="background: linear-gradient(135deg, #0f172a 0%, #155E63 60%, #0d9488 100%) !important;">
                    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-12 w-40 h-40 rounded-full bg-teal-300/10 blur-xl pointer-events-none"></div>

                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-teal-200/80 font-semibold block">Rekening Pencairan &amp; Payroll</span>
                            <h3 class="text-base sm:text-lg font-extrabold tracking-tight mt-0.5"
                                x-text="bankName ? bankName.toUpperCase() : 'BELUM DIATUR'"></h3>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="relative z-10 my-5">
                        <span class="text-[10px] uppercase tracking-wider text-teal-100/70 block mb-1">Nomor Rekening</span>
                        <p class="font-mono text-lg sm:text-xl font-bold tracking-widest text-white"
                           x-text="accountNumber ? accountNumber : '•••• •••• ••••'"></p>
                    </div>

                    <div class="relative z-10 flex items-center justify-between pt-3 border-t border-white/15 text-xs">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-teal-100/70 block">Atas Nama</span>
                            <span class="font-bold text-white uppercase tracking-wide"
                                  x-text="accountHolder ? accountHolder : '{{ $user->name }}'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase tracking-wider text-teal-100/70 block">WhatsApp Aktif</span>
                            <span class="font-mono font-semibold text-teal-100" x-text="phoneVal ? phoneVal : '-'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (lg:col-span-7): Form Update Kontak, Rekening & Password -->
            <div class="lg:col-span-7">
                <form method="POST"
                      action="{{ route('portal.profile.update') }}"
                      enctype="multipart/form-data"
                      class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_8px_30px_rgb(15,23,42,0.04)] p-5 sm:p-7 space-y-7">
                    @csrf
                    @method('PUT')

                    <!-- Hidden File Input triggered by Avatar Camera button -->
                    <input type="file"
                           id="profile-foto-input"
                           name="foto"
                           accept="image/*"
                           class="hidden"
                           @change="handlePhotoChange($event)">

                    <!-- SECTION 1: Kontak & Rekening Pencairan -->
                    <div class="space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">Kontak &amp; Rekening Pencairan</h2>
                                <p class="text-[11px] text-slate-500">Digunakan untuk notifikasi WhatsApp dan pencairan kasbon / reimbursement</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <!-- Nomor WhatsApp -->
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1.5">
                                    Nomor Telepon / WhatsApp Aktif <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <input type="text"
                                           name="phone"
                                           x-model="phoneVal"
                                           required
                                           placeholder="Contoh: 081234567890"
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-10 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                </div>
                            </div>

                            <!-- Nama Bank -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Nama Bank</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                    </div>
                                    <input type="text"
                                           name="bank"
                                           x-model="bankName"
                                           placeholder="Contoh: BCA, Mandiri, BRI"
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-10 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                </div>
                                <!-- Quick Select Bank Chips -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @foreach (['BCA', 'Mandiri', 'BRI', 'BNI', 'BSI', 'SeaBank'] as $presetBank)
                                        <button type="button"
                                                @click="bankName = '{{ $presetBank }}'"
                                                :class="bankName.toUpperCase() === '{{ strtoupper($presetBank) }}' ? 'bg-[#155E63] text-white border-[#155E63]' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200/70'"
                                                class="px-2 py-0.5 rounded-lg text-[10px] font-bold border transition cursor-pointer">
                                            {{ $presetBank }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Nomor Rekening -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Nomor Rekening</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <input type="text"
                                           name="no_rekening"
                                           x-model="accountNumber"
                                           placeholder="Contoh: 1234567890"
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-10 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-mono font-semibold text-slate-800 transition">
                                </div>
                            </div>

                            <!-- Atas Nama Rekening -->
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1.5">Nama Pemilik Rekening (Atas Nama)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <input type="text"
                                           name="atas_nama"
                                           x-model="accountHolder"
                                           placeholder="Nama persis sesuai buku tabungan"
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-10 pr-3.5 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Keamanan & Ubah Password -->
                    <div class="space-y-5 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">Keamanan &amp; Ubah Password</h2>
                                <p class="text-[11px] text-slate-500">Biarkan kosong jika Anda tidak ingin mengubah password akun saat ini</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <!-- Password Saat Ini -->
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                                <div class="relative">
                                    <input :type="showCurrentPass ? 'text' : 'password'"
                                           name="current_password"
                                           placeholder="Masukkan password lama untuk konfirmasi..."
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                    <button type="button"
                                            @click="showCurrentPass = !showCurrentPass"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg x-show="!showCurrentPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showCurrentPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Password Baru -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Password Baru (Min. 8 Karakter)</label>
                                <div class="relative">
                                    <input :type="showNewPass ? 'text' : 'password'"
                                           name="new_password"
                                           placeholder="Password baru..."
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                    <button type="button"
                                            @click="showNewPass = !showNewPass"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg x-show="!showNewPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showNewPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                                <div class="relative">
                                    <input :type="showConfirmPass ? 'text' : 'password'"
                                           name="new_password_confirmation"
                                           placeholder="Ulangi password baru..."
                                           class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-[#155E63] font-medium text-slate-800 transition">
                                    <button type="button"
                                            @click="showConfirmPass = !showConfirmPass"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showConfirmPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-[11px] text-slate-400">
                            Setiap perubahan data tercatat secara otomatis pada sistem keamanan Portal MSN.
                        </p>
                        <button type="submit"
                                style="background: linear-gradient(135deg, #093c40 0%, #155E63 55%, #0d9488 100%) !important;"
                                class="w-full sm:w-auto justify-center hover:opacity-95 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl shadow-md shadow-teal-900/15 transition flex items-center gap-2 cursor-pointer shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
