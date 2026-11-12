<x-layouts.app title="Masuk ke Akun — GoMSN">
    <div class="h-screen h-[100dvh] max-h-screen w-full flex flex-col lg:flex-row select-none overflow-hidden" 
         style="background-color: #F8FAFC; background-image: radial-gradient(rgba(148, 163, 184, 0.35) 1px, transparent 1px); background-size: 24px 24px;"
         x-data="{ showPw: false }">
        
        <!-- ============================================================== -->
        <!-- LEFT PANEL (DESKTOP) / TOP BANNER (MOBILE) - THEMA SIDEBAR TEAL -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 h-[34vh] min-h-[170px] lg:h-full shrink-0 relative flex flex-col justify-between py-2 sm:py-4 lg:py-16 px-4 sm:px-8 lg:px-12 xl:px-16 text-center text-white z-10 overflow-hidden"
             style="background: linear-gradient(165deg, #072d31 0%, #0c4349 20%, #155E63 50%, #17555B 80%, #1F2937 100%);">
            
            <!-- Specular Gloss Highlight Rims -->
            <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/60 to-transparent pointer-events-none"></div>
            <div class="absolute inset-y-0 left-0 w-[1px] bg-gradient-to-b from-white/10 via-white/5 to-transparent pointer-events-none"></div>

            <!-- Subtle Micro-texture & Ambient Glow on Green Panel -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.18]"
                 style="background-image: radial-gradient(rgba(94, 234, 212, 0.4) 1px, transparent 1px), linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
                        background-size: 24px 24px, 48px 48px, 48px 48px;"></div>
            <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-teal-400/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

            <!-- DESKTOP ORGANIC CURVED WAVE (RIGHT EDGE) COVERED IN FULL DOT GRID -->
            <div class="hidden lg:block absolute -right-[2px] top-0 bottom-0 h-full w-24 xl:w-32 z-30 pointer-events-none"
                 style="background-color: #F8FAFC; 
                        background-image: radial-gradient(rgba(148, 163, 184, 0.35) 1px, transparent 1px); 
                        background-size: 24px 24px; 
                        background-position: calc(100% - 2px) top;
                        -webkit-mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 1000' preserveAspectRatio='none'%3E%3Cpath d='M 0 0 C 18 200, 55 340, 20 500 C -10 660, 48 800, 0 1000 L 130 1000 L 130 0 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        mask-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 1000' preserveAspectRatio='none'%3E%3Cpath d='M 0 0 C 18 200, 55 340, 20 500 C -10 660, 48 800, 0 1000 L 130 1000 L 130 0 Z' fill='black' /%3E%3C/svg%3E&quot;);
                        -webkit-mask-size: 100% 100%;
                        mask-size: 100% 100%;
                        -webkit-mask-repeat: no-repeat;
                        mask-repeat: no-repeat;">
            </div>

            <!-- MOBILE HORIZONTAL WAVE (BOTTOM EDGE) COVERED IN FULL DOT GRID -->
            <div class="lg:hidden absolute -bottom-[1px] left-0 right-0 w-full h-6 sm:h-9 pointer-events-none z-30"
                 style="background-color: #F8FAFC; 
                        background-image: radial-gradient(rgba(148, 163, 184, 0.35) 1px, transparent 1px); 
                        background-size: 24px 24px; 
                        background-position: left calc(100% - 1px);
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
            <div class="relative z-10 my-auto py-1 sm:py-2 lg:py-4">
                <!-- Text: Selamat Datang di -->
                <p class="text-white text-xs sm:text-base lg:text-xl font-bold tracking-tight mb-1 sm:mb-2.5 lg:mb-4">
                    Selamat Datang di
                </p>

                <!-- Circular Logo Badge matching Reference -->
                <div class="mx-auto w-12 h-12 sm:w-20 sm:h-20 lg:w-32 lg:h-32 rounded-full bg-white shadow-[0_8px_24px_rgba(2,40,46,0.35)] flex items-center justify-center p-2 sm:p-3 lg:p-4 mb-1 sm:mb-2.5 lg:mb-4 ring-2 sm:ring-4 ring-white/20 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                         alt="GoMSN" 
                         class="h-7 sm:h-12 lg:h-20 w-auto max-w-[46px] sm:max-w-[70px] lg:max-w-[100px] object-contain">
                </div>

                <!-- App Name: GoMSN -->
                <h1 class="font-heading text-lg sm:text-2xl lg:text-4xl font-extrabold text-white tracking-tight mb-0.5 sm:mb-1.5 lg:mb-2 leading-tight">
                    GoMSN
                </h1>

                <!-- Subtitle / Deskripsi Sistem -->
                <p class="text-[10px] sm:text-xs lg:text-sm text-teal-100/90 leading-tight sm:leading-relaxed line-clamp-2 max-w-[280px] sm:max-w-md lg:max-w-lg mx-auto font-normal">
                    Sistem manajemen gangguan, tiket operasional &amp; pelaporan kerja jaringan terpadu PT Media Solusi Network.
                </p>
            </div>

            <!-- Bottom Brand Text (Desktop) -->
            <div class="hidden lg:block pt-4 relative z-10">
                <span class="text-[11px] font-bold tracking-widest text-teal-200/50 uppercase">
                    PT MEDIA SOLUSI NETWORK
                </span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- RIGHT PANEL: LOGIN FORM (50% DESKTOP / 66% MOBILE)             -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 h-[66vh] lg:h-full flex-1 flex flex-col justify-center items-center px-5 sm:px-8 lg:p-12 xl:p-16 relative overflow-hidden -mt-[1px] lg:mt-0"
             style="background-color: #F8FAFC; background-image: radial-gradient(rgba(148, 163, 184, 0.35) 1px, transparent 1px); background-size: 24px 24px; background-position: left top;">
            
            <div class="w-full max-w-[340px] sm:max-w-md lg:max-w-lg xl:max-w-xl mx-auto my-auto relative z-10">
                
                <!-- Heading Masuk ke Akun (Rata Tengah) -->
                <div class="mb-3 sm:mb-5 lg:mb-7 text-center">
                    <h2 class="font-heading text-xl sm:text-2xl lg:text-[32px] xl:text-[36px] font-extrabold text-slate-900 tracking-tight leading-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="text-[11px] sm:text-xs lg:text-sm xl:text-base text-slate-500 mt-0.5 sm:mt-1.5 font-normal max-w-md mx-auto">
                        Silakan masukkan kredensial akun Anda untuk login.
                    </p>
                </div>

                <!-- Info Alert / Status Flash Message -->
                @if (session('status'))
                <div class="mb-2.5 sm:mb-4 p-2.5 sm:p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="truncate">{{ session('status') }}</span>
                </div>
                @elseif (session('error'))
                <div class="mb-2.5 sm:mb-4 p-2.5 sm:p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="truncate">{{ session('error') }}</span>
                </div>
                @elseif ($errors->any())
                <div class="mb-2.5 sm:mb-4 p-2.5 sm:p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-2xs">
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
                <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-2.5 sm:space-y-3.5 lg:space-y-5">
                    @csrf
                    <input type="hidden" name="platform" value="web">

                    <!-- Field 1: Email atau Username -->
                    <div>
                        <label for="identifier" class="block text-[11px] sm:text-xs lg:text-sm font-semibold text-slate-700 mb-1 sm:mb-1.5 text-left">
                            Email atau Username
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-3.5 lg:pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                required
                                autofocus
                                value="{{ old('identifier') }}"
                                placeholder="Masukkan email, username, atau NIK"
                                class="w-full pl-9 sm:pl-10 lg:pl-12 pr-4 py-2 sm:py-2.5 lg:py-3.5 rounded-xl sm:rounded-2xl border border-slate-300 bg-white text-xs sm:text-sm lg:text-base text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition-all"
                            >
                        </div>
                    </div>

                    <!-- Field 2: Kata Sandi (Password) -->
                    <div>
                        <label for="password" class="block text-[11px] sm:text-xs lg:text-sm font-semibold text-slate-700 mb-1 sm:mb-1.5 text-left">
                            Kata Sandi (Password)
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-3.5 lg:pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                name="password"
                                :type="showPw ? 'text' : 'password'"
                                required
                                value=""
                                placeholder="Masukkan kata sandi"
                                class="w-full pl-9 sm:pl-10 lg:pl-12 pr-10 sm:pr-11 lg:pr-12 py-2 sm:py-2.5 lg:py-3.5 rounded-xl sm:rounded-2xl border border-slate-300 bg-white text-xs sm:text-sm lg:text-base text-slate-800 focus:outline-none focus:border-[#155E63] focus:ring-2 focus:ring-[#18C4C4]/25 shadow-2xs transition-all font-mono"
                            >
                            <!-- Show / Hide Password Eye Icon -->
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute inset-y-0 right-0 pr-3 sm:pr-3.5 lg:pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                title="Lihat kata sandi"
                                tabindex="-1"
                            >
                                <svg x-show="!showPw" class="w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPw" x-cloak class="w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Checkbox Ingat Saya -->
                    <div class="flex items-center justify-between text-[11px] sm:text-xs lg:text-sm text-slate-600 select-none pt-0.5">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" name="remember" checked class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-slate-300 text-[#155E63] focus:ring-teal-600">
                            <span>Ingat saya di perangkat ini (30 Hari)</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk ke Dashboard -->
                    <button type="submit" 
                            style="background: linear-gradient(135deg, #093c40 0%, #155E63 50%, #0d9488 100%) !important;"
                            class="w-full py-2.5 sm:py-3 lg:py-4 px-4 sm:px-6 rounded-xl sm:rounded-2xl text-white font-bold text-xs sm:text-sm lg:text-base flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(21,94,99,0.35)] hover:shadow-lg hover:brightness-105 active:scale-[0.99] transition-all cursor-pointer">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Masuk ke Dashboard</span>
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-layouts.app>
