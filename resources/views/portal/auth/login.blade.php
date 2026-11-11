<x-layouts.app title="Masuk ke Akun — GoMSN">
    <div class="min-h-screen w-full flex flex-col lg:flex-row select-none overflow-x-hidden" 
         style="background-color: #F8FAFC; background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px); background-size: 20px 20px;"
         x-data="{ 
        showPw: false,
        moreRolesOpen: false,
        quickLogin(identifier, password) {
            const idField = document.getElementById('identifier');
            const pwField = document.getElementById('password');
            if (idField && pwField) {
                idField.value = identifier;
                pwField.value = password;
                idField.classList.add('ring-2', 'ring-teal-500', 'bg-teal-50/50');
                pwField.classList.add('ring-2', 'ring-teal-500', 'bg-teal-50/50');
                setTimeout(() => {
                    idField.classList.remove('ring-2', 'ring-teal-500', 'bg-teal-50/50');
                    pwField.classList.remove('ring-2', 'ring-teal-500', 'bg-teal-50/50');
                }, 400);
            }
        }
    }">
        <!-- ============================================================== -->
        <!-- LEFT PANEL (DESKTOP) / TOP BANNER (MOBILE) - THEMA SIDEBAR TEAL (50%) -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 shrink-0 relative flex flex-col justify-between py-10 px-6 sm:px-10 lg:py-16 text-center text-white z-10"
             style="background: linear-gradient(165deg, #072d31 0%, #0c4349 20%, #155E63 50%, #17555B 80%, #1F2937 100%);">
            
            <!-- Specular Gloss Highlight Rims -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/60 to-transparent pointer-events-none"></div>
            <div class="absolute inset-y-0 left-0 w-[1px] bg-gradient-to-b from-white/10 via-white/5 to-transparent pointer-events-none"></div>

            <!-- DESKTOP ORGANIC CURVED WAVE (RIGHT EDGE) COVERED IN FULL DOT GRID -->
            <div class="hidden lg:block absolute -right-[2px] top-0 bottom-0 h-full w-24 xl:w-32 z-30 pointer-events-none"
                 style="background-color: #F8FAFC; 
                        background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px); 
                        background-size: 20px 20px; 
                        background-position: calc(100% - 2px) top;
                        -webkit-mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 1000' preserveAspectRatio='none'%3E%3Cpath d='M 0 0 C 18 200, 55 340, 20 500 C -10 660, 48 800, 0 1000 L 130 1000 L 130 0 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 1000' preserveAspectRatio='none'%3E%3Cpath d='M 0 0 C 18 200, 55 340, 20 500 C -10 660, 48 800, 0 1000 L 130 1000 L 130 0 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        -webkit-mask-size: 100% 100%;
                        mask-size: 100% 100%;
                        -webkit-mask-repeat: no-repeat;
                        mask-repeat: no-repeat;">
            </div>

            <!-- MOBILE HORIZONTAL WAVE (BOTTOM EDGE) COVERED IN FULL DOT GRID -->
            <div class="lg:hidden absolute -bottom-[2px] left-0 right-0 w-full h-14 sm:h-20 pointer-events-none z-30"
                 style="background-color: #F8FAFC; 
                        background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px); 
                        background-size: 20px 20px; 
                        background-position: left calc(100% - 2px);
                        -webkit-mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1000 120' preserveAspectRatio='none'%3E%3Cpath d='M 0 68 C 180 96, 480 42, 740 78 C 880 96, 950 68, 1000 72 L 1000 130 L 0 130 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1000 120' preserveAspectRatio='none'%3E%3Cpath d='M 0 68 C 180 96, 480 42, 740 78 C 880 96, 950 68, 1000 72 L 1000 130 L 0 130 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        -webkit-mask-size: 100% 100%;
                        mask-size: 100% 100%;
                        -webkit-mask-repeat: no-repeat;
                        mask-repeat: no-repeat;">
            </div>

            <!-- Top Spacer on Desktop for Balanced Vertical Centering -->
            <div class="hidden lg:block h-6"></div>

            <!-- Center Content: Greeting, Circular Logo, GoMSN Title, Description -->
            <div class="relative z-10 my-auto py-4">
                <!-- Text: Selamat Datang di -->
                <p class="text-white text-base sm:text-lg lg:text-xl font-bold tracking-tight mb-4 sm:mb-5">
                    Selamat Datang di
                </p>

                <!-- Circular Logo Badge matching Reference -->
                <div class="mx-auto w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-white shadow-[0_12px_32px_rgba(2,40,46,0.38)] flex items-center justify-center p-3.5 mb-4 sm:mb-5 ring-4 ring-white/20 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                         alt="GoMSN" 
                         class="h-14 sm:h-16 w-auto max-w-[85px] object-contain">
                </div>

                <!-- App Name: GoMSN (Bukan Work Report) -->
                <h1 class="font-heading text-2xl sm:text-3xl lg:text-[32px] font-extrabold text-white tracking-tight mb-2 sm:mb-3 leading-tight">
                    GoMSN
                </h1>

                <!-- Subtitle / Deskripsi Sistem -->
                <p class="text-xs sm:text-[13px] text-teal-100/85 leading-relaxed max-w-xs sm:max-w-md mx-auto font-normal">
                    Sistem manajemen gangguan, tiket operasional &amp; pelaporan kerja jaringan terpadu PT Media Solusi Network.
                </p>
            </div>

            <!-- Bottom Brand Text (Desktop) -->
            <div class="hidden lg:block pt-6 relative z-10">
                <span class="text-[11px] font-bold tracking-widest text-teal-200/50 uppercase">
                    PT MEDIA SOLUSI NETWORK
                </span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- RIGHT PANEL: LOGIN FORM & FAST DEMO ACCOUNT ACCESS (50%)       -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 flex-1 flex flex-col justify-center items-center py-8 px-4 sm:px-8 lg:p-12 relative min-h-[500px] -mt-[1px] lg:mt-0"
             style="background-color: #F8FAFC; background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px); background-size: 20px 20px; background-position: left top;">
            
            <div class="w-full max-w-md mx-auto relative z-10">
                
                <!-- Heading Masuk ke Akun -->
                <div class="mb-5 sm:mb-6 text-left">
                    <h2 class="font-heading text-2xl sm:text-[26px] font-extrabold text-slate-900 tracking-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="text-xs sm:text-[13px] text-slate-500 mt-1 font-normal">
                        Silakan masukkan kredensial akun Anda untuk login.
                    </p>
                </div>

                <!-- Info Alert / Status Flash Message -->
                @if (session('status'))
                <div class="mb-4 p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-2.5 shadow-2xs">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
                @elseif (session('error'))
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @elseif ($errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-2xs">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Kredensial tidak valid:</span>
                    </div>
                    <ul class="list-disc list-inside text-rose-700 pl-1 text-[11px]">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form Login -->
                <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="platform" value="web">

                    <!-- Field 1: Email atau Username (dengan Icon ID Badge) -->
                    <div>
                        <label for="identifier" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Email atau Username
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                required
                                autofocus
                                value="{{ old('identifier', 'KR24521') }}"
                                placeholder="Ipin Aripin / KR24521"
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition-all"
                            >
                        </div>
                    </div>

                    <!-- Field 2: Kata Sandi (Password) dengan Lock & Eye Toggle -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata Sandi (Password)
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                name="password"
                                :type="showPw ? 'text' : 'password'"
                                required
                                value="MsnPortal#2026"
                                placeholder="Kata sandi"
                                class="w-full pl-10 pr-11 py-2.5 sm:py-3 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition-all font-mono"
                            >
                            <!-- Show / Hide Password Eye Icon -->
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                title="Lihat kata sandi"
                                tabindex="-1"
                            >
                                <svg x-show="!showPw" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPw" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Checkbox Ingat Saya & Hint Password -->
                    <div class="flex items-center justify-between text-xs text-slate-600 select-none pt-0.5">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded border-slate-300 text-[#155E63] focus:ring-teal-600">
                            <span>Ingat saya di perangkat ini (30 Hari)</span>
                        </label>
                        <span class="font-mono text-[11px] text-slate-400 hidden sm:inline" title="Kata sandi akun uji coba">
                            Pass: <strong class="text-slate-600 font-semibold">MsnPortal#2026</strong>
                        </span>
                    </div>

                    <!-- Tombol Masuk ke Dashboard (Gradient Tema Sidebar) -->
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                            class="w-full py-3 px-4 rounded-xl text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(21,94,99,0.35)] hover:opacity-95 transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Masuk ke Dashboard</span>
                    </button>
                </form>

                <!-- Divider AKSES CEPAT DEMO AKUN -->
                <div class="relative my-6 flex items-center justify-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <span class="relative bg-[#F8FAFC] px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        AKSES CEPAT DEMO AKUN
                    </span>
                </div>

                <!-- 3 Quick Demo Cards (Persis Gambar Mockup) -->
                <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
                    <!-- Card 1: Admin NOC / IT -->
                    <button type="button" 
                            @click="quickLogin('KR00010', 'MsnPortal#2026')"
                            class="p-3 rounded-xl bg-white border border-slate-200/90 hover:border-teal-500 hover:shadow-md transition text-center cursor-pointer group flex flex-col items-center justify-center shadow-2xs">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="block text-[11px] font-bold text-slate-800 group-hover:text-teal-700 leading-tight">Admin NOC</span>
                        <span class="block text-[9px] text-slate-400 font-medium mt-0.5">Full System</span>
                    </button>

                    <!-- Card 2: HelpDesk NOC -->
                    <button type="button" 
                            @click="quickLogin('KR31444', 'MsnPortal#2026')"
                            class="p-3 rounded-xl bg-white border border-slate-200/90 hover:border-teal-500 hover:shadow-md transition text-center cursor-pointer group flex flex-col items-center justify-center shadow-2xs">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                            </svg>
                        </div>
                        <span class="block text-[11px] font-bold text-slate-800 group-hover:text-teal-700 leading-tight">HelpDesk NOC</span>
                        <span class="block text-[9px] text-slate-400 font-medium mt-0.5">Tiket &amp; Dispatch</span>
                    </button>

                    <!-- Card 3: Tim Teknis (Ipin Aripin) -->
                    <button type="button" 
                            @click="quickLogin('KR24521', 'MsnPortal#2026')"
                            class="p-3 rounded-xl bg-white border border-slate-200/90 hover:border-teal-500 hover:shadow-md transition text-center cursor-pointer group flex flex-col items-center justify-center shadow-2xs">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="block text-[11px] font-bold text-slate-800 group-hover:text-teal-700 leading-tight">Tim Teknis</span>
                        <span class="block text-[9px] text-slate-400 font-medium mt-0.5">Lapangan &amp; JC</span>
                    </button>
                </div>

                <!-- Expander untuk 7 Role Lainnya (Finance, HRD, Atasan Umum, CEO, dll) -->
                <div class="mt-3 text-center">
                    <button type="button" 
                            @click="moreRolesOpen = !moreRolesOpen"
                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-teal-700 hover:text-teal-900 transition cursor-pointer">
                        <span x-text="moreRolesOpen ? 'Tutup Pilihan Role' : 'Pilihan Role Lainnya (HRD, Finance, CEO...)'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="moreRolesOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="moreRolesOpen" x-cloak class="mt-2.5 grid grid-cols-2 gap-2 text-left pt-2 border-t border-slate-100">
                        @php
                            $extraPersonas = [
                                ['id' => 'KR6465', 'label' => 'Finance', 'name' => 'Karmelia D.', 'divisi' => 'Finance', 'icon' => '💳'],
                                ['id' => 'KR48567', 'label' => 'HRD', 'name' => 'Amelia Agustina', 'divisi' => 'HRD', 'icon' => '👥'],
                                ['id' => 'KR00071', 'label' => 'CEO', 'name' => 'YUDIANA ARIFIN', 'divisi' => 'Direksi', 'icon' => '🏛️'],
                                ['id' => 'KR59446', 'label' => 'Atasan NOC', 'name' => 'Bagus Joko P.', 'divisi' => 'NOC', 'icon' => '📡'],
                                ['id' => 'KR64654', 'label' => 'Atasan Umum', 'name' => 'Ida', 'divisi' => 'Umum', 'icon' => '📋'],
                                ['id' => 'KR20268', 'label' => 'Karyawan Teknik', 'name' => 'Christian Perdi', 'divisi' => 'Teknik', 'icon' => '👷'],
                                ['id' => 'KR51462', 'label' => 'Karyawan Umum', 'name' => 'Depi Sopandi', 'divisi' => 'Umum', 'icon' => '📦'],
                            ];
                        @endphp
                        @foreach ($extraPersonas as $ep)
                        <button type="button"
                                @click="quickLogin('{{ $ep['id'] }}', 'MsnPortal#2026')"
                                class="p-2 rounded-lg bg-white border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 transition cursor-pointer flex items-center justify-between text-xs shadow-2xs">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span>{{ $ep['icon'] }}</span>
                                <span class="font-bold text-slate-800 text-[11px] truncate">{{ $ep['label'] }}</span>
                            </div>
                            <span class="text-[9px] font-mono text-slate-400">{{ $ep['id'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
