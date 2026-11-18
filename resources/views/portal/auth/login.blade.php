<x-layouts.app title="Masuk ke Akun — GoMSN">
    <style>
        html, body {
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            overscroll-behavior: none !important;
            width: 100% !important;
        }
        #main-content {
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            min-height: unset !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .login-wrap {
            display: flex;
            position: fixed !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            overflow: hidden !important;
            background-color: #FFFFFF;
            background-image: radial-gradient(rgba(148, 163, 184, 0.35) 1.2px, transparent 1.2px);
            background-size: 22px 22px;
            user-select: none;
        }
        .login-panel-left {
            width: 49%;
            height: 100%;
            flex-shrink: 0;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 60px 30px 48px;
            background: radial-gradient(120% 120% at 20% 20%, #072d31 0%, #0c4349 35%, #155E63 70%, #0a353a 100%);
            color: #FFFFFF;
            text-align: center;
            z-index: 20;
        }
        .login-panel-right {
            width: 51%;
            height: 100%;
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 32px 48px 20px 48px;
            overflow: hidden;
            z-index: 10;
        }
        .input-stylish {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border-radius: 14px;
            border: 1.5px solid #E2E8F0;
            background-color: #F8FAFC;
            color: #0F172A;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            outline: none;
        }
        .input-stylish:focus {
            background-color: #FFFFFF;
            border-color: #155E63;
            box-shadow: 0 0 0 4px rgba(21, 94, 99, 0.12);
        }
        .input-stylish::placeholder {
            color: #94A3B8;
            font-weight: 400;
        }
        .action-row {
            display: flex;
            align-items: stretch;
            gap: 10px;
            width: 100%;
        }
        .btn-stylish {
            flex: 1;
            min-height: 48px;
            padding: 12px 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #072d31 0%, #155E63 50%, #0d9488 100%);
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 22px -4px rgba(21, 94, 99, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-stylish:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 12px 26px -4px rgba(21, 94, 99, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.35);
        }
        .btn-stylish:active {
            transform: translateY(0);
        }
        .btn-biometric {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            padding: 0;
            border-radius: 14px;
            background: #FFFFFF;
            border: 1.5px solid rgba(21, 94, 99, 0.28);
            color: #155E63;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px -4px rgba(15, 23, 42, 0.08);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-biometric:hover:not(:disabled) {
            background: #F0FDFA;
            border-color: #155E63;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -6px rgba(21, 94, 99, 0.25);
        }
        .btn-biometric:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        .wave-desktop {
            display: block;
            position: absolute;
            right: -1px;
            top: -1px;
            bottom: -1px;
            width: 105px;
            height: calc(100% + 2px);
            z-index: 30;
            pointer-events: none;
        }
        .wave-mobile {
            display: none;
            position: absolute;
            bottom: -1px;
            left: -1px;
            right: -1px;
            width: calc(100% + 2px);
            height: 64px;
            z-index: 30;
            pointer-events: none;
        }

        /* Hide all scrollbar chrome */
        .login-wrap::-webkit-scrollbar,
        .login-panel-right::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .login-wrap,
        .login-panel-right,
        body {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Brand Circular Logo Badge (Fixed dimensions to prevent any overflow) */
        .brand-logo-badge {
            width: 76px;
            height: 76px;
            min-width: 76px;
            min-height: 76px;
            max-width: 76px;
            max-height: 76px;
            border-radius: 9999px;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 8px 24px rgba(2, 40, 46, 0.35);
            border: 3px solid rgba(255, 255, 255, 0.25);
            margin: 0 auto;
            position: relative;
            transition: transform 0.3s ease;
        }
        @media (min-width: 1024px) {
            .brand-logo-badge {
                width: 96px;
                height: 96px;
                min-width: 96px;
                min-height: 96px;
                max-width: 96px;
                max-height: 96px;
                padding: 12px;
            }
        }
        .brand-logo-badge:hover {
            transform: scale(1.05);
        }
        .brand-logo-img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Responsive Mobile / Tablet (< 1024px) */
        @media (max-width: 1023px) {
            .wave-desktop {
                display: none !important;
            }
            .wave-mobile {
                display: block !important;
                height: 38px !important;
            }
            .login-wrap {
                flex-direction: column !important;
                height: 100vh !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
                overflow: hidden !important;
            }
            .brand-logo-badge {
                width: 58px !important;
                height: 58px !important;
                min-width: 58px !important;
                min-height: 58px !important;
                max-width: 58px !important;
                max-height: 58px !important;
                padding: 8px !important;
            }
            .login-panel-left {
                width: 100% !important;
                height: 38% !important;
                height: 38dvh !important;
                max-height: 260px !important;
                min-height: 200px !important;
                flex: 0 0 auto !important;
                padding: 14px 20px 42px 20px !important;
                justify-content: space-around !important;
            }
            .login-panel-right {
                width: 100% !important;
                height: 62% !important;
                height: 62dvh !important;
                flex: 1 1 auto !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 8px 24px 12px 24px !important;
                margin-top: 0 !important;
                z-index: 25 !important;
                overflow: hidden !important;
            }
            .input-stylish {
                padding: 11px 14px 11px 40px !important;
                border-radius: 12px !important;
                font-size: 13.5px !important;
            }
            .btn-stylish {
                min-height: 46px !important;
                padding: 11px 16px !important;
                border-radius: 12px !important;
                font-size: 14px !important;
            }
            .btn-biometric {
                width: 46px !important;
                height: 46px !important;
                border-radius: 12px !important;
            }
        }

        /* Ultra small mobile screens (height <= 640px) */
        @media (max-height: 640px) and (max-width: 1023px) {
            .brand-logo-badge {
                width: 48px !important;
                height: 48px !important;
                min-width: 48px !important;
                min-height: 48px !important;
                max-width: 48px !important;
                max-height: 48px !important;
                padding: 6px !important;
            }
            .login-panel-left {
                height: 34% !important;
                height: 34dvh !important;
                min-height: 170px !important;
                padding: 10px 14px 34px 14px !important;
            }
            .login-panel-right {
                height: 66% !important;
                height: 66dvh !important;
                padding: 10px 18px 8px 18px !important;
            }
            .wave-mobile {
                height: 30px !important;
            }
        }
    </style>

    <div class="login-wrap" x-data="{
        showPw: false,
        biometricLoading: false,
        biometricError: '',
        hasRegisteredBiometric: false,
        init() {
            try {
                const creds = JSON.parse(localStorage.getItem('gomsn_biometric_creds') || '[]');
                this.hasRegisteredBiometric = Array.isArray(creds) && creds.length > 0 && !!window.PublicKeyCredential;
            } catch (e) {
                this.hasRegisteredBiometric = false;
            }
        },
        b64UrlToUint8(b64url) {
            const pad = '='.repeat((4 - (b64url.length % 4)) % 4);
            const b64 = (b64url + pad).replace(/-/g, '+').replace(/_/g, '/');
            const bin = atob(b64);
            const bytes = new Uint8Array(bin.length);
            for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
            return bytes;
        },
        bufToB64Url(buf) {
            const bytes = new Uint8Array(buf);
            let bin = '';
            for (let i = 0; i < bytes.byteLength; i++) bin += String.fromCharCode(bytes[i]);
            return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        },
        async loginWithBiometric() {
            this.biometricError = '';
            if (!window.PublicKeyCredential) {
                this.biometricError = 'Browser atau perangkat ini belum mendukung login biometrik WebAuthn / Passkey.';
                return;
            }
            if (!window.isSecureContext) {
                this.biometricError = 'Login biometrik membutuhkan koneksi HTTPS yang aman.';
                return;
            }
            this.biometricLoading = true;
            try {
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const identifier = (document.getElementById('identifier')?.value || '').trim();
                let localCreds = [];
                try {
                    localCreds = JSON.parse(localStorage.getItem('gomsn_biometric_creds') || '[]');
                } catch (e) {}

                const optRes = await fetch('{{ route('login.webauthn.options') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ identifier: identifier || null, local_credential_ids: localCreds })
                });
                const opts = await optRes.json();
                if (!optRes.ok) throw new Error(opts.message || 'Gagal menyiapkan autentikasi biometrik.');

                if (identifier && (!opts.allowCredentials || opts.allowCredentials.length === 0)) {
                    throw new Error('Akun ini belum mengaktifkan Login Biometrik. Masuk dengan password dulu lalu aktifkan di menu Profil Saya.');
                }

                const publicKey = {
                    ...opts,
                    challenge: this.b64UrlToUint8(opts.challenge),
                    allowCredentials: (opts.allowCredentials || []).map(c => ({
                        ...c,
                        id: this.b64UrlToUint8(c.id)
                    }))
                };

                const assertion = await navigator.credentials.get({ publicKey });
                if (!assertion) throw new Error('Verifikasi biometrik dibatalkan.');

                const verifyRes = await fetch('{{ route('login.webauthn.verify') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({
                        credential_id: this.bufToB64Url(assertion.rawId),
                        client_data_json: this.bufToB64Url(assertion.response.clientDataJSON),
                        authenticator_data: this.bufToB64Url(assertion.response.authenticatorData),
                        signature: this.bufToB64Url(assertion.response.signature)
                    })
                });
                const verifyJson = await verifyRes.json();
                if (!verifyRes.ok) throw new Error(verifyJson.message || 'Verifikasi biometrik gagal.');

                window.location.href = verifyJson.redirect || '/';
            } catch (err) {
                let msg = err.message || 'Login biometrik gagal.';
                if (err.name === 'NotAllowedError') {
                    msg = 'Sensor biometrik dibatalkan atau belum ada Passkey aktif di perangkat ini. Masuk dengan password terlebih dahulu lalu aktifkan Biometrik di menu Profil Saya.';
                }
                this.biometricError = msg;
            } finally {
                this.biometricLoading = false;
            }
        }
    }">
        
        <!-- ============================================================== -->
        <!-- LEFT PANEL: HERO BRAND WITH ICONIC ORGANIC WAVE                -->
        <!-- ============================================================== -->
        <div class="login-panel-left">
            
            <!-- Clipped Background Atmosphere (Glows & Micro-Grid) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#5eead4]/60 to-transparent"></div>
                <div class="absolute inset-y-0 left-0 w-[1px] bg-gradient-to-b from-white/10 via-white/5 to-transparent"></div>
                <div class="absolute inset-0 opacity-[0.2]"
                     style="background-image: radial-gradient(rgba(94, 234, 212, 0.45) 1px, transparent 1px), linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
                            background-size: 22px 22px, 44px 44px, 44px 44px;"></div>
                <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-teal-400/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-emerald-400/15 blur-3xl"></div>
            </div>

            <!-- DESKTOP MULTI-LAYER ORGANIC CURVED WAVES (RIGHT EDGE) -->
            <svg class="wave-desktop" viewBox="0 0 120 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Layer 1: Medium Vibrant Teal Ribbon -->
                <path d="M 14,0 C 50,150 58,310 42,490 C 26,670 58,830 44,1000 L 122,1000 L 122,0 Z" fill="rgba(20, 184, 166, 0.55)" />
                <!-- Layer 2: Soft Pastel Mint/Cyan Ribbon -->
                <path d="M 32,0 C 68,150 76,310 60,490 C 44,670 76,830 62,1000 L 122,1000 L 122,0 Z" fill="rgba(153, 246, 228, 0.78)" />
                <!-- Layer 3: Solid White Foreground -->
                <path d="M 50,0 C 86,150 94,310 78,490 C 62,670 94,830 80,1000 L 122,1000 L 122,0 Z" fill="#FFFFFF" />
            </svg>

            <!-- MOBILE MULTI-LAYER HORIZONTAL WAVES (BOTTOM EDGE) -->
            <svg class="wave-mobile" viewBox="0 0 1000 106" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Layer 1: Medium Vibrant Teal Ribbon -->
                <path d="M 0,18 C 160,38 320,34 480,48 C 640,64 780,74 900,56 C 950,48 980,50 1000,56 L 1000,108 L 0,108 Z" fill="rgba(20, 184, 166, 0.55)" />
                <!-- Layer 2: Soft Pastel Mint/Cyan Ribbon -->
                <path d="M 0,36 C 160,56 320,52 480,66 C 640,82 780,92 900,74 C 950,66 980,68 1000,74 L 1000,108 L 0,108 Z" fill="rgba(153, 246, 228, 0.78)" />
                <!-- Layer 3: Solid White Foreground -->
                <path d="M 0,54 C 160,74 320,70 480,84 C 640,100 780,106 900,92 C 950,84 980,86 1000,92 L 1000,108 L 0,108 Z" fill="#FFFFFF" />
            </svg>

            <!-- Top Spacer for Vertical Balance -->
            <div class="hidden lg:block h-4"></div>

            <!-- Center Content: Logo, Title, Description -->
            <div class="relative z-10 my-auto py-1 sm:py-2">
                <!-- Text: Selamat Datang di -->
                <p class="text-teal-200/90 text-xs sm:text-sm font-bold tracking-wider uppercase mb-1.5 sm:mb-2.5">
                    Selamat Datang di
                </p>

                <!-- Circular Logo Badge with Ambient Glow -->
                <div class="relative inline-block mx-auto mb-2 sm:mb-3 group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-teal-400/30 to-emerald-400/30 rounded-full blur-md opacity-75 group-hover:opacity-100 transition duration-300"></div>
                    <div class="brand-logo-badge">
                        <img src="{{ asset('assets/logo/logo-msn2.png') }}" 
                             alt="GoMSN" 
                             class="brand-logo-img">
                    </div>
                </div>

                <!-- App Name: GoMSN -->
                <h1 class="font-heading text-2xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-1 leading-tight">
                    Go<span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-200 via-teal-300 to-emerald-300">MSN</span>
                </h1>

                <!-- Subtitle / Deskripsi Sistem -->
                <p class="text-xs sm:text-sm text-teal-50/85 leading-snug max-w-xs sm:max-w-sm mx-auto font-normal">
                    Sistem Operasional &amp; Manajemen Jaringan Terpadu
                </p>
            </div>

            <!-- Bottom Brand Text -->
            <div class="relative z-10 pt-1.5 pb-0.5 flex items-center justify-center text-[10px] sm:text-xs font-semibold tracking-wider text-teal-200/50 uppercase border-t border-white/10 lg:mb-0">
                <span>PT MEDIA SOLUSI NETWORK</span>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- RIGHT PANEL: CLEAN, SLEEK & ELEGANT LOGIN FORM                 -->
        <!-- ============================================================== -->
        <div class="login-panel-right">
            
            <div class="w-full max-w-[380px] sm:max-w-[420px] mx-auto mt-1 sm:my-auto mb-auto relative z-10">
                
                <!-- Heading Masuk ke Akun -->
                <div class="mb-2 sm:mb-4 text-center">
                    <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Silakan masukkan kredensial akun Anda untuk login.
                    </p>
                </div>

                <!-- Info Alert / Status Flash Message -->
                @if (session('status'))
                <div class="mb-2.5 p-2.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-[11px] sm:text-xs flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="truncate">{{ session('status') }}</span>
                </div>
                @elseif (session('error'))
                <div class="mb-2.5 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] sm:text-xs flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="truncate">{{ session('error') }}</span>
                </div>
                @elseif ($errors->any())
                <div class="mb-2.5 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-0.5 shadow-2xs">
                    <div class="font-bold flex items-center gap-1.5 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Kredensial tidak valid:</span>
                    </div>
                    <ul class="list-disc list-inside text-rose-700 pl-1 text-[10.5px]">
                        @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Biometric Inline Error Alert -->
                <div x-show="biometricError" x-cloak class="mb-2.5 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] flex items-start gap-2 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="leading-relaxed" x-text="biometricError"></span>
                </div>

                <!-- Form Login -->
                <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-2 sm:space-y-3.5">
                    @csrf
                    <input type="hidden" name="platform" value="web">

                    <!-- Field 1: Email atau Username -->
                    <div>
                        <label for="identifier" class="block text-[10.5px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 text-left">
                            Username
                        </label>
                        <div class="relative flex items-center group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-700 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                placeholder="Masukkan username"
                                class="input-stylish"
                            >
                        </div>
                    </div>

                    <!-- Field 2: Kata Sandi (Password) -->
                    <div>
                        <label for="password" class="block text-[10.5px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 text-left">
                            Kata Sandi (Password)
                        </label>
                        <div class="relative flex items-center group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-700 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                class="input-stylish"
                                style="padding-right: 40px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;"
                            >
                            <!-- Show / Hide Password Eye Toggle -->
                            <button
                                type="button"
                                @click="showPw = !showPw"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-teal-700 cursor-pointer focus:outline-none transition-colors"
                                title="Lihat kata sandi"
                                tabindex="-1"
                            >
                                <svg x-show="!showPw" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPw" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Checkbox Ingat Saya -->
                    <div class="flex items-center justify-between text-[11px] sm:text-xs text-slate-600 select-none pt-0.5">
                        <label class="inline-flex items-center gap-1.5 sm:gap-2 cursor-pointer font-medium hover:text-slate-800 transition-colors">
                            <input type="checkbox" name="remember" checked class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600 focus:ring-offset-0">
                            <span>Ingat saya di perangkat ini (30 Hari)</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk ke Dashboard + Ikon Biometrik di Samping -->
                    <div class="action-row pt-0.5">
                        <button type="submit" class="btn-stylish">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Masuk ke Dashboard</span>
                        </button>

                        <button type="button"
                                x-show="hasRegisteredBiometric"
                                x-cloak
                                @click="loginWithBiometric()"
                                :disabled="biometricLoading"
                                title="Masuk dengan Sidik Jari / Biometrik"
                                aria-label="Masuk dengan Sidik Jari / Biometrik"
                                class="btn-biometric">
                            <svg x-show="!biometricLoading" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                            </svg>
                            <svg x-show="biometricLoading" x-cloak class="w-4.5 h-4.5 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

            </div>

            <!-- Footer Hak Cipta & Keamanan Portal (Mengisi Bagian Bawah Layar HP) -->
            <div class="w-full text-center pt-1.5 pb-0.5 relative z-10 select-none">
                <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium inline-flex items-center gap-1.5 justify-center">
                    <svg class="w-3.5 h-3.5 text-teal-600/70 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>GoMSN Secure Portal &bull; PT Media Solusi Network</span>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
