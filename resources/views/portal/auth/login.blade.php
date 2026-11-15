<x-layouts.app title="Masuk ke Akun — GoMSN">
    <div class="min-h-screen min-h-[100dvh] w-full flex flex-col lg:flex-row select-none overflow-x-hidden bg-[#FAFCFC]"
         x-data="{ showPw: false }">
        
        <!-- ============================================================== -->
        <!-- LEFT PANEL (DESKTOP) / TOP BANNER (MOBILE) - HERO SHOWCASE    -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-[48%] xl:w-[46%] shrink-0 relative flex flex-col justify-between py-8 px-6 sm:px-10 lg:py-12 lg:px-12 xl:px-14 text-center lg:text-left text-white z-20 overflow-hidden"
             style="background: radial-gradient(130% 130% at 20% 15%, #062b2f 0%, #0a3a40 35%, #155E63 70%, #031b1e 100%);">
            
            <!-- Atmospheric Lighting & Specular Rims -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <!-- Top & Side Precision Hairline Glow -->
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/60 to-transparent"></div>
                <div class="hidden lg:block absolute inset-y-0 right-0 w-[1px] bg-gradient-to-b from-transparent via-[#5eead4]/30 to-transparent"></div>
                
                <!-- Micro Grid Overlay -->
                <div class="absolute inset-0 opacity-[0.2]"
                     style="background-image: radial-gradient(rgba(94, 234, 212, 0.4) 1.2px, transparent 1.2px), linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
                            background-size: 24px 24px, 48px 48px, 48px 48px;"></div>
                
                <!-- Sculpted Light Flares -->
                <div class="absolute -top-28 -left-28 w-96 h-96 rounded-full bg-teal-400/20 blur-[90px]"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-emerald-400/15 blur-[100px]"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-[#18C4C4]/10 blur-[80px]"></div>
            </div>

            <!-- Top Header: Status Pill -->
            <div class="relative z-10 flex items-center justify-center lg:justify-start">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm text-xs font-semibold text-teal-100">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span>Portal Operasional Terpadu</span>
                </div>
            </div>

            <!-- Center Brand & Feature Showcase -->
            <div class="relative z-10 my-auto py-6 sm:py-8">
                <!-- Circular Logo Badge with Ambient Glow -->
                <div class="relative inline-block mx-auto lg:mx-0 mb-4 group">
                    <div class="absolute -inset-2 bg-gradient-to-r from-teal-400/30 via-emerald-400/20 to-teal-500/30 rounded-3xl blur-xl opacity-75 group-hover:opacity-100 transition duration-500"></div>
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white shadow-[0_12px_36px_rgba(2,40,46,0.45)] ring-4 ring-white/20 p-4 flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                        <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                             alt="GoMSN" 
                             class="w-full h-full object-contain">
                    </div>
                </div>

                <!-- App Name: GoMSN -->
                <div class="mb-2">
                    <p class="text-teal-200/90 text-xs sm:text-sm font-semibold tracking-wide uppercase mb-1">
                        Selamat Datang di
                    </p>
                    <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        Go<span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-200 via-teal-300 to-emerald-300">MSN</span>
                    </h1>
                </div>

                <!-- Description -->
                <p class="text-xs sm:text-sm text-teal-50/85 font-normal leading-relaxed max-w-md mx-auto lg:mx-0">
                    Sistem manajemen gangguan, tiket operasional &amp; pelaporan kerja jaringan terpadu PT Media Solusi Network.
                </p>

                <!-- Modern Feature Bento Highlights (Desktop / Tablet) -->
                <div class="hidden sm:grid grid-cols-1 gap-2.5 max-w-md mx-auto lg:mx-0 mt-6 text-left">
                    <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/[0.06] backdrop-blur-md border border-white/10 hover:bg-white/[0.1] transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-teal-500/20 border border-teal-400/30 flex items-center justify-center shrink-0 text-teal-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white tracking-wide">Monitoring Jaringan &amp; NOC</div>
                            <div class="text-[11px] text-teal-100/70 truncate">Penanganan tiket gangguan &amp; koordinasi tim lapangan 24/7</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/[0.06] backdrop-blur-md border border-white/10 hover:bg-white/[0.1] transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-teal-500/20 border border-teal-400/30 flex items-center justify-center shrink-0 text-teal-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white tracking-wide">Presensi Biometrik &amp; SDM</div>
                            <div class="text-[11px] text-teal-100/70 truncate">Verifikasi wajah biometrik terdaftar &amp; radius akurat GPS</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3.5 p-3 rounded-xl bg-white/[0.06] backdrop-blur-md border border-white/10 hover:bg-white/[0.1] transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-teal-500/20 border border-teal-400/30 flex items-center justify-center shrink-0 text-teal-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white tracking-wide">Pengajuan Kas &amp; Lembur</div>
                            <div class="text-[11px] text-teal-100/70 truncate">Persetujuan hierarkis digital &amp; pencairan transparan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Brand & Security Footer -->
            <div class="relative z-10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 border-t border-white/10 text-[11px] text-teal-200/60 font-medium">
                <span class="tracking-wider uppercase font-semibold">
                    PT MEDIA SOLUSI NETWORK
                </span>
                <span class="inline-flex items-center gap-1.5 text-teal-300/80">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    256-Bit SSL Secured
                </span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- RIGHT PANEL: LOGIN CARD & FORM (LUXURY FINTECH / SAAS STYLE)   -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-[52%] xl:w-[54%] flex-1 flex flex-col justify-center items-center py-8 sm:py-12 lg:py-16 px-4 sm:px-8 lg:px-12 relative overflow-hidden bg-[#FAFCFC]">
            
            <!-- Subtle Radial Ambience in Background -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.25]"
                     style="background-image: radial-gradient(rgba(148, 163, 184, 0.4) 1px, transparent 1px);
                            background-size: 22px 22px;"></div>
            </div>

            <!-- Floating Login Card Container -->
            <div class="w-full max-w-[440px] sm:max-w-[460px] relative z-10">
                <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 sm:p-9 border border-slate-200/90 shadow-[0_20px_50px_-12px_rgba(15,23,42,0.08)] ring-1 ring-slate-900/5">
                    
                    <!-- Card Header -->
                    <div class="mb-6 text-center">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-teal-50 text-teal-800 border border-teal-200/70 mb-2.5">
                            <svg class="w-3.5 h-3.5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 6.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Portal Karyawan
                        </div>
                        <h2 class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Masuk ke Akun
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Silakan masukkan kredensial akun Anda untuk login.
                        </p>
                    </div>

                    <!-- Flash Message / Alerts -->
                    @if (session('status'))
                    <div class="mb-4 p-3 sm:p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs sm:text-sm flex items-center gap-2.5 shadow-2xs">
                        <svg class="w-4.5 h-4.5 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="truncate">{{ session('status') }}</span>
                    </div>
                    @elseif (session('error'))
                    <div class="mb-4 p-3 sm:p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center gap-2.5 shadow-2xs">
                        <svg class="w-4.5 h-4.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="truncate">{{ session('error') }}</span>
                    </div>
                    @elseif ($errors->any())
                    <div class="mb-4 p-3 sm:p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-2xs">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
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

                        <!-- Field 1: Identifier -->
                        <div>
                            <label for="identifier" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 text-left">
                                Email atau Username
                            </label>
                            <div class="relative flex items-center group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 shadow-2xs transition-all font-medium"
                                >
                            </div>
                        </div>

                        <!-- Field 2: Password -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 text-left">
                                Kata Sandi (Password)
                            </label>
                            <div class="relative flex items-center group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                    class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 focus:bg-white text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 shadow-2xs transition-all font-mono"
                                >
                                <!-- Show / Hide Password Toggle -->
                                <button
                                    type="button"
                                    @click="showPw = !showPw"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-teal-700 cursor-pointer focus:outline-none transition-colors"
                                    title="Lihat kata sandi"
                                    tabindex="-1"
                                >
                                    <svg x-show="!showPw" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPw" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox Ingat Saya -->
                        <div class="flex items-center justify-between text-xs sm:text-sm text-slate-600 select-none pt-0.5">
                            <label class="inline-flex items-center gap-2 cursor-pointer font-medium hover:text-slate-800 transition-colors">
                                <input type="checkbox" name="remember" checked class="w-4 h-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600 focus:ring-offset-0">
                                <span>Ingat saya di perangkat ini (30 Hari)</span>
                            </label>
                        </div>

                        <!-- Tombol Masuk ke Dashboard -->
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #072d31 0%, #155E63 55%, #0d9488 100%) !important;"
                                class="w-full relative group py-3.5 px-6 rounded-xl text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2.5 shadow-[0_8px_20px_-4px_rgba(21,94,99,0.38)] hover:shadow-[0_12px_24px_-4px_rgba(21,94,99,0.48)] hover:brightness-105 active:scale-[0.99] transition-all cursor-pointer overflow-hidden">
                            <!-- Inner Top Highlight -->
                            <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                            
                            <svg class="w-5 h-5 text-white transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Masuk ke Dashboard</span>
                        </button>
                    </form>

                    <!-- Card Footer: Support Notice -->
                    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                        <p class="text-[11px] text-slate-500 font-normal">
                            Mengalami kendala akun? Hubungi <span class="font-semibold text-teal-700">Divisi IT &amp; SDM</span>
                        </p>
                    </div>

                </div>
            </div>

            <!-- Page Bottom Copyright (Mobile / Small Screen) -->
            <div class="lg:hidden text-center mt-6 text-[11px] text-slate-500 font-medium">
                © 2026 PT Media Solusi Network • All Rights Reserved
            </div>

        </div>
    </div>
</x-layouts.app>
