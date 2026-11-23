<x-layouts.app title="Masuk ke Akun — GoMSN">
    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            width: 100% !important;
            background-color: #042f36;
            overflow: hidden !important;
        }
        #main-content {
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .gomsn-login-page {
            position: relative;
            height: 100% !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            overflow: hidden !important;
            background: linear-gradient(180deg, #eef7f9 0%, #f7fbfc 60%, #e4f4f5 100%);
        }

        /* =========================================================
           HERO BACKDROP (MOBILE: TOP HALF | DESKTOP: LEFT CURVED HALF)
           ========================================================= */
        .gomsn-hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 54%;
            min-height: 340px;
            background:
                radial-gradient(circle at 86% 14%, rgba(25, 211, 197, 0.42) 0%, rgba(13, 148, 136, 0.16) 32%, transparent 62%),
                radial-gradient(circle at 18% 18%, rgba(12, 92, 98, 0.55) 0%, transparent 55%),
                linear-gradient(145deg, #032c33 0%, #05444c 42%, #08686d 78%, #0a7f82 100%);
            z-index: 0;
            pointer-events: none;
        }

        /* Subtle Top-Left Dot Matrix */
        .gomsn-dot-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 240px;
            height: 220px;
            background-image: radial-gradient(rgba(153, 246, 228, 0.32) 1.4px, transparent 1.4px);
            background-size: 16px 16px;
            mask-image: radial-gradient(ellipse at 10% 10%, black 15%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse at 10% 10%, black 15%, transparent 72%);
            opacity: 0.7;
            pointer-events: none;
        }

        /* Sweeping Diagonal Light Beams in Hero */
        .gomsn-hero-beam {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(125deg, transparent 22%, rgba(45, 212, 191, 0.08) 38%, rgba(20, 184, 166, 0.14) 50%, transparent 65%),
                linear-gradient(155deg, transparent 48%, rgba(94, 234, 212, 0.08) 62%, transparent 76%);
            pointer-events: none;
        }

        /* Network Wireframe Globe */
        .gomsn-globe {
            position: absolute;
            top: -24px;
            right: -40px;
            width: 290px;
            height: 290px;
            opacity: 0.50;
            pointer-events: none;
        }

        /* Mid Wave Divider (Mobile/Tablet Horizontal) */
        .gomsn-mid-wave-mobile {
            display: block;
            position: absolute;
            bottom: -2px;
            left: -2px;
            right: -2px;
            width: calc(100% + 4px);
            height: 115px;
            pointer-events: none;
        }

        /* Mid Wave Divider (Desktop Vertical Organic Curve) */
        .gomsn-mid-wave-desktop {
            display: none;
            position: absolute;
            top: -2px;
            bottom: -2px;
            right: -2px;
            width: 150px;
            height: calc(100% + 4px);
            pointer-events: none;
        }

        /* Faint City Skyline Silhouettes on Lower Edges */
        .gomsn-skyline-left,
        .gomsn-skyline-right {
            position: absolute;
            bottom: 0;
            width: 140px;
            height: 105px;
            opacity: 0.12;
            pointer-events: none;
            z-index: 0;
        }
        .gomsn-skyline-left { left: 0; }
        .gomsn-skyline-right { right: 0; transform: scaleX(-1); }

        /* Circular White Logo Badge */
        .gomsn-logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 9999px;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            box-shadow: 0 12px 32px rgba(2, 34, 40, 0.32), 0 0 0 5px rgba(255, 255, 255, 0.14);
            margin: 0 auto;
            transition: transform 0.25s ease;
        }
        .gomsn-logo-circle:hover {
            transform: scale(1.04);
        }

        /* Floating Login Card */
        .gomsn-card {
            position: relative;
            width: 100%;
            max-width: 430px;
            background: #FFFFFF;
            border-radius: 26px;
            box-shadow:
                0 24px 60px -16px rgba(4, 47, 54, 0.22),
                0 6px 20px -6px rgba(4, 47, 54, 0.08),
                0 0 0 1px rgba(15, 118, 110, 0.08);
            overflow: hidden;
        }

        /* Input Styling matching reference (clean single focus border + autofill fix) */
        .gomsn-input {
            width: 100%;
            height: 45px;
            padding: 0 16px 0 42px;
            border-radius: 12px;
            border: 1.5px solid #D9E5EE;
            background-color: #F1F6FA;
            color: #0F2433;
            font-size: 14px;
            font-weight: 500;
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            outline: none !important;
            -webkit-appearance: none;
        }
        .gomsn-input:focus {
            background-color: #FFFFFF;
            border-color: #0F8B8D;
            box-shadow: 0 0 0 3px rgba(15, 139, 141, 0.16);
            outline: none !important;
        }
        .gomsn-input:-webkit-autofill,
        .gomsn-input:-webkit-autofill:hover,
        .gomsn-input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #F1F6FA inset !important;
            -webkit-text-fill-color: #0F2433 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        .gomsn-input:focus:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset, 0 0 0 3px rgba(15, 139, 141, 0.16) !important;
        }
        .gomsn-input::placeholder {
            color: #8A9BAD;
            font-weight: 400;
        }

        /* Custom Checkbox */
        .gomsn-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 1.5px solid #CBD5E1;
            background-color: #FFFFFF;
            display: inline-grid;
            place-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .gomsn-checkbox:checked {
            background-color: #1A73E8;
            border-color: #1A73E8;
        }
        .gomsn-checkbox:checked::before {
            content: "";
            width: 10px;
            height: 10px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='4 10.5 8 14.5 16 6.5'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .gomsn-checkbox:focus-visible {
            outline: 2px solid #1A73E8;
            outline-offset: 2px;
        }

        /* Primary Submit Button */
        .gomsn-btn-submit {
            position: relative;
            flex: 1;
            height: 49px;
            padding: 0 20px;
            border-radius: 14px;
            background: linear-gradient(90deg, #043239 0%, #075259 48%, #0b8a87 100%);
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow:
                0 12px 26px -6px rgba(6, 82, 89, 0.48),
                inset 0 1px 0 rgba(255, 255, 255, 0.22);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .gomsn-btn-submit:hover {
            filter: brightness(1.07);
            transform: translateY(-1px);
            box-shadow:
                0 16px 30px -6px rgba(6, 82, 89, 0.56),
                inset 0 1px 0 rgba(255, 255, 255, 0.28);
        }
        .gomsn-btn-submit:active {
            transform: translateY(0);
        }

        .gomsn-btn-biometric {
            width: 49px;
            height: 49px;
            flex-shrink: 0;
            border-radius: 14px;
            background: #F1F8F8;
            border: 1.5px solid rgba(11, 138, 135, 0.3);
            color: #085E63;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .gomsn-btn-biometric:hover:not(:disabled) {
            background: #E2F5F4;
            border-color: #0B8A87;
            transform: translateY(-1px);
        }
        .gomsn-btn-biometric:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Safety compaction only on very short screens (e.g. <= 640px height) */
        @media (max-height: 640px) {
            .gomsn-input {
                height: 41px;
                font-size: 13.5px;
            }
            .gomsn-btn-submit {
                height: 44px;
                font-size: 14px;
            }
            .gomsn-btn-biometric {
                height: 44px;
                width: 44px;
            }
        }

        /* =========================================================
           TABLET & DESKTOP RESPONSIVE (>= 1024px)
           Two-column balanced layout fitting 100% in 1 viewport!
           ========================================================= */
        @media (min-width: 1024px) {
            .gomsn-hero-bg {
                top: 0;
                bottom: 0;
                left: 0;
                right: auto;
                width: 55%;
                height: 100%;
                min-height: 100dvh;
                background:
                    radial-gradient(circle at 76% 24%, rgba(25, 211, 197, 0.34) 0%, rgba(13, 148, 136, 0.14) 36%, transparent 65%),
                    radial-gradient(circle at 18% 20%, rgba(12, 92, 98, 0.52) 0%, transparent 55%),
                    linear-gradient(155deg, #032b32 0%, #05444c 45%, #08686d 82%, #0a7f82 100%);
            }
            .gomsn-mid-wave-mobile {
                display: none;
            }
            .gomsn-mid-wave-desktop {
                display: block;
            }
            .gomsn-globe {
                top: -24px;
                right: 32px;
                transform: none;
                width: 390px;
                height: 390px;
                opacity: 0.38;
            }
            .gomsn-skyline-left {
                display: none;
            }
            .gomsn-skyline-right {
                width: 170px;
                height: 120px;
                opacity: 0.12;
                bottom: 0;
                right: 0;
                transform: scaleX(-1);
            }
            .gomsn-logo-circle {
                width: 104px;
                height: 104px;
                padding: 18px;
                box-shadow: 0 18px 40px rgba(2, 34, 40, 0.38), 0 0 0 7px rgba(255, 255, 255, 0.14);
            }
        }
    </style>

    <div class="gomsn-login-page" x-data="{
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
        <!-- LAYER 1: DARK TEAL HERO BACKGROUND + GLOBE + ORGANIC WAVES     -->
        <!-- ============================================================== -->
        <div class="gomsn-hero-bg">
            <div class="gomsn-dot-grid"></div>
            <div class="gomsn-hero-beam"></div>

            <!-- Futuristic Glowing Wireframe Network Globe -->
            <svg class="gomsn-globe" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <radialGradient id="globeGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.32" />
                        <stop offset="65%" stop-color="#0d9488" stop-opacity="0.10" />
                        <stop offset="100%" stop-color="#042f36" stop-opacity="0" />
                    </radialGradient>
                    <linearGradient id="meshStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#99f6e4" stop-opacity="0.7" />
                        <stop offset="50%" stop-color="#2dd4bf" stop-opacity="0.38" />
                        <stop offset="100%" stop-color="#0d9488" stop-opacity="0.12" />
                    </linearGradient>
                </defs>
                <circle cx="210" cy="195" r="165" fill="url(#globeGlow)" />
                <circle cx="210" cy="195" r="142" stroke="url(#meshStroke)" stroke-width="1.2" />
                <ellipse cx="210" cy="195" rx="142" ry="52" stroke="url(#meshStroke)" stroke-width="0.9" />
                <ellipse cx="210" cy="195" rx="142" ry="102" stroke="url(#meshStroke)" stroke-width="0.85" />
                <ellipse cx="210" cy="195" rx="92" ry="142" stroke="url(#meshStroke)" stroke-width="0.85" />
                <ellipse cx="210" cy="195" rx="44" ry="142" stroke="url(#meshStroke)" stroke-width="0.85" />
                <line x1="68" y1="195" x2="352" y2="195" stroke="url(#meshStroke)" stroke-width="0.8" />
                <line x1="210" y1="53" x2="210" y2="337" stroke="url(#meshStroke)" stroke-width="0.8" />
                <path d="M126 112 L192 94 L274 118 L322 178 L286 246 L210 264 L132 232 L98 168 Z" stroke="#5eead4" stroke-opacity="0.45" stroke-width="0.9" />
                <path d="M126 112 L210 156 L274 118 M210 156 L286 246 M210 156 L132 232 M192 94 L210 156 L322 178" stroke="#99f6e4" stroke-opacity="0.38" stroke-width="0.85" />
                <circle cx="126" cy="112" r="3.2" fill="#ccfbf1" />
                <circle cx="192" cy="94" r="2.6" fill="#5eead4" />
                <circle cx="274" cy="118" r="3.5" fill="#ccfbf1" />
                <circle cx="210" cy="156" r="4.2" fill="#ffffff" />
                <circle cx="210" cy="156" r="9" fill="#2dd4bf" fill-opacity="0.28" />
                <circle cx="322" cy="178" r="3" fill="#5eead4" />
                <circle cx="286" cy="246" r="3.4" fill="#99f6e4" />
                <circle cx="210" cy="264" r="2.6" fill="#5eead4" />
                <circle cx="132" cy="232" r="3.2" fill="#99f6e4" />
                <circle cx="98" cy="168" r="2.8" fill="#ccfbf1" />
                <circle cx="155" cy="195" r="2.2" fill="#5eead4" />
                <circle cx="268" cy="195" r="2.4" fill="#ccfbf1" />
            </svg>

            <!-- Mobile/Tablet Horizontal Wave Transition -->
            <svg class="gomsn-mid-wave-mobile" viewBox="0 0 1440 220" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M0,128 C280,55 560,15 860,68 C1110,112 1300,96 1440,34 L1440,220 L0,220 Z" fill="rgba(45, 212, 191, 0.24)" />
                <path d="M0,152 C320,76 620,42 930,92 C1165,130 1330,104 1440,56 L1440,220 L0,220 Z" fill="rgba(204, 251, 241, 0.45)" />
                <path d="M0,174 C340,94 670,66 980,114 C1200,148 1350,122 1440,82 L1440,220 L0,220 Z" fill="#eef7f9" />
            </svg>

            <!-- Desktop Vertical Curved Wave Transition (Left Teal -> Right Soft Ice-White) -->
            <svg class="gomsn-mid-wave-desktop" viewBox="0 0 160 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M22,0 C78,180 96,350 62,530 C30,700 84,860 54,1000 L162,1000 L162,0 Z" fill="rgba(45, 212, 191, 0.25)" />
                <path d="M46,0 C102,180 120,350 86,530 C54,700 108,860 78,1000 L162,1000 L162,0 Z" fill="rgba(204, 251, 241, 0.55)" />
                <path d="M70,0 C126,180 144,350 110,530 C78,700 132,860 102,1000 L162,1000 L162,0 Z" fill="#f3f9fb" />
            </svg>
        </div>

        <!-- Subtle City Skyline Silhouettes on Lower Edges -->
        <svg class="gomsn-skyline-left" viewBox="0 0 175 220" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="12" y="90" width="26" height="130" rx="2" fill="#94B8C4" />
            <rect x="44" y="52" width="32" height="168" rx="2" fill="#82ABB8" />
            <rect x="82" y="110" width="24" height="110" rx="2" fill="#A5C4CE" />
            <rect x="112" y="72" width="30" height="148" rx="2" fill="#94B8C4" />
            <rect x="148" y="128" width="22" height="92" rx="2" fill="#B2CED7" />
            <rect x="52" y="64" width="6" height="6" fill="#eef7f9" />
            <rect x="62" y="64" width="6" height="6" fill="#eef7f9" />
            <rect x="52" y="78" width="6" height="6" fill="#eef7f9" />
            <rect x="62" y="78" width="6" height="6" fill="#eef7f9" />
            <rect x="120" y="84" width="6" height="6" fill="#eef7f9" />
            <rect x="130" y="84" width="6" height="6" fill="#eef7f9" />
        </svg>
        <svg class="gomsn-skyline-right" viewBox="0 0 175 220" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="12" y="90" width="26" height="130" rx="2" fill="#94B8C4" />
            <rect x="44" y="52" width="32" height="168" rx="2" fill="#82ABB8" />
            <rect x="82" y="110" width="24" height="110" rx="2" fill="#A5C4CE" />
            <rect x="112" y="72" width="30" height="148" rx="2" fill="#94B8C4" />
            <rect x="148" y="128" width="22" height="92" rx="2" fill="#B2CED7" />
            <rect x="52" y="64" width="6" height="6" fill="#eef7f9" />
            <rect x="62" y="64" width="6" height="6" fill="#eef7f9" />
            <rect x="52" y="78" width="6" height="6" fill="#eef7f9" />
            <rect x="62" y="78" width="6" height="6" fill="#eef7f9" />
            <rect x="120" y="84" width="6" height="6" fill="#eef7f9" />
            <rect x="130" y="84" width="6" height="6" fill="#eef7f9" />
        </svg>

        <!-- ============================================================== -->
        <!-- LAYER 2: RESPONSIVE MAIN CONTAINER                             -->
        <!-- Mobile/Tablet: Vertical Stack | Desktop: Side-by-Side 2-Column -->
        <!-- ============================================================== -->
        <div class="relative z-10 w-full max-w-[1140px] flex-1 flex flex-col lg:flex-row items-center justify-center lg:justify-between gap-4 sm:gap-6 lg:gap-12 xl:gap-16 px-4 sm:px-6 lg:px-12 pt-4 sm:pt-6 lg:py-6 pb-3 sm:pb-5">

            <!-- Brand Header (Top on Mobile, Centered Left Column on Desktop) -->
            <div class="w-full max-w-[430px] lg:max-w-none lg:w-[46%] text-center select-none lg:pr-6 shrink-0">
                <!-- Company Name with flanking thin lines -->
                <div class="flex items-center justify-center gap-3 mb-2.5 sm:mb-3.5 lg:mb-5">
                    <span class="h-[1px] w-10 sm:w-14 lg:w-16 bg-gradient-to-r from-transparent to-white/45"></span>
                    <span class="text-[11px] sm:text-xs lg:text-[13px] font-bold tracking-[0.16em] text-white/95 uppercase">
                        PT. MEDIA SOLUSI NETWORK
                    </span>
                    <span class="h-[1px] w-10 sm:w-14 lg:w-16 bg-gradient-to-l from-transparent to-white/45"></span>
                </div>

                <!-- Brand Logo GoMSN (Matching Sidebar) -->
                <div class="mb-2 sm:mb-3 lg:mb-4 flex items-center justify-center">
                    <h1 class="sr-only">GoMSN - PT Media Solusi Network</h1>
                    <img src="{{ asset('assets/logo/logo1.png') }}"
                         alt="GoMSN"
                         style="filter: brightness(0) invert(1) !important;"
                         class="h-14 sm:h-16 lg:h-20 w-auto max-w-[260px] sm:max-w-[300px] lg:max-w-[360px] object-contain drop-shadow-[0_4px_16px_rgba(0,0,0,0.35)] transition-transform duration-200 hover:scale-105">
                </div>

                <!-- Italic Tagline -->
                <p class="mt-1.5 lg:mt-2.5 text-[12.5px] sm:text-[13.5px] lg:text-[15px] italic text-teal-50/90 font-normal tracking-wide max-w-md mx-auto">
                    Melayani dengan Solusi, Terhubung untuk Masa Depan
                </p>

                <!-- Desktop-only Subtle System Pill -->
                <div class="hidden lg:inline-flex items-center gap-2 mt-6 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 backdrop-blur-xs text-[12px] text-teal-100/90 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Portal Operasional &amp; HRIS Terpadu</span>
                </div>

                <!-- Desktop-only Copyright under Pill Badge -->
                <p class="hidden lg:block mt-3.5 text-[12px] text-teal-100/80 font-normal tracking-wide">
                    &copy; {{ date('Y') }} PT. Media Solusi Network. All Rights Reserved.
                </p>
            </div>

            <!-- Floating White Login Card (Bottom on Mobile, Right Column on Desktop) -->
            <div class="w-full lg:w-[50%] flex justify-center lg:justify-end shrink-0">
                <div class="gomsn-card">
                    <!-- Signature Top Curved Teal Wave Ribbon inside Card -->
                    <div class="relative w-full h-9 sm:h-11 overflow-hidden pointer-events-none select-none">
                        <svg class="w-full h-full block" viewBox="0 0 460 64" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <defs>
                                <linearGradient id="cardTopWaveGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#0b9b96" />
                                    <stop offset="48%" stop-color="#14b8a6" />
                                    <stop offset="100%" stop-color="#08918c" />
                                </linearGradient>
                                <linearGradient id="cardTopWaveSoft" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#99f6e4" stop-opacity="0.55" />
                                    <stop offset="55%" stop-color="#ccfbf1" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#5eead4" stop-opacity="0.35" />
                                </linearGradient>
                            </defs>
                            <path d="M0,0 L460,0 L460,20 C385,36 320,26 240,14 C150,0 72,22 0,58 Z" fill="url(#cardTopWaveSoft)" />
                            <path d="M0,0 L460,0 L460,12 C392,28 334,14 252,4 C168,-6 80,8 0,34 Z" fill="url(#cardTopWaveGrad)" />
                        </svg>
                    </div>

                    <!-- Card Body -->
                    <div class="px-6 sm:px-8 pb-5 sm:pb-6 -mt-3 relative z-10">
                        <!-- Lock Icon Badge with subtle side lines -->
                        <div class="flex items-center justify-center gap-4 mb-2.5">
                            <span class="h-[1px] flex-1 bg-gradient-to-r from-transparent via-slate-200 to-slate-200"></span>
                            <div class="w-11 h-11 rounded-full bg-[#e3f7f5] border border-[#cdeee9] flex items-center justify-center shadow-2xs shrink-0">
                                <svg class="w-5 h-5 text-[#0b5e63]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="5" y="11" width="14" height="10" rx="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="16" r="1.2" fill="currentColor" stroke="none"/>
                                </svg>
                            </div>
                            <span class="h-[1px] flex-1 bg-gradient-to-l from-transparent via-slate-200 to-slate-200"></span>
                        </div>

                        <!-- Card Heading -->
                        <div class="text-center mb-4">
                            <h2 style="color: #0b2333 !important;" class="font-heading text-[23px] sm:text-[26px] font-extrabold text-[#0b2333] tracking-tight leading-tight">
                                Masuk ke Akun
                            </h2>
                            <p style="color: #64748b !important;" class="text-[12.5px] sm:text-[13px] text-slate-500 mt-1">
                                Silakan login untuk mengakses layanan GOMSN
                            </p>
                        </div>

                        <!-- Status / Error Alerts -->
                        @if (session('status'))
                        <div class="mb-3 p-2.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                        @elseif (session('error'))
                        <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        @elseif ($errors->any())
                        <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
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

                        <!-- Biometric Error Alert -->
                        <div x-show="biometricError" x-cloak class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                            <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="leading-relaxed" x-text="biometricError"></span>
                        </div>

                        <!-- Login Form -->
                        <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-3.5">
                            @csrf
                            <input type="hidden" name="platform" value="web">

                            <!-- Field 1: Username -->
                            <div>
                                <label for="identifier" class="flex items-center gap-2 text-[13px] font-bold text-[#0f2937] mb-1.5">
                                    <svg class="w-4 h-4 text-[#0e7478] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Username</span>
                                </label>
                                <div class="relative flex items-center group">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#0e7478]/85 group-focus-within:text-[#0b5e63] transition-colors">
                                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="identifier"
                                        name="identifier"
                                        type="text"
                                        required
                                        autofocus
                                        value="{{ old('identifier') }}"
                                        placeholder="Ketik Username Anda"
                                        class="gomsn-input"
                                    >
                                </div>
                            </div>

                            <!-- Field 2: Kata Sandi (Password) -->
                            <div>
                                <label for="password" class="flex items-center gap-2 text-[13px] font-bold text-[#0f2937] mb-1.5">
                                    <svg class="w-4 h-4 text-[#0e7478] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Kata Sandi (Password)</span>
                                </label>
                                <div class="relative flex items-center group">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#0b5e63] transition-colors">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="5" y="11" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        name="password"
                                        :type="showPw ? 'text' : 'password'"
                                        required
                                        value=""
                                        placeholder="Masukkan Kata Sandi Anda"
                                        class="gomsn-input"
                                        style="padding-right: 44px;"
                                    >
                                    <button
                                        type="button"
                                        @click="showPw = !showPw"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-[#0b5e63] cursor-pointer focus:outline-none transition-colors"
                                        title="Tampilkan / Sembunyikan kata sandi"
                                        tabindex="-1"
                                    >
                                        <svg x-show="!showPw" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                        <svg x-show="showPw" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Checkbox Ingat Saya -->
                            <div class="pt-0.5">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none text-[13px] font-medium text-[#1e293b] hover:text-slate-900">
                                    <input type="checkbox" name="remember" checked class="gomsn-checkbox">
                                    <span>Ingat saya di perangkat ini (3 Bulan)</span>
                                </label>
                            </div>

                            <!-- Submit Button + Optional Biometric Button -->
                            <div class="flex items-center gap-2.5 pt-0.5">
                                <button type="submit" class="gomsn-btn-submit">
                                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l4-4m0 0l-4-4m4 4H3m6-7V5a2 2 0 012-2h7a2 2 0 012 2v14a2 2 0 01-2 2h-7a2 2 0 01-2-2v-2"/>
                                    </svg>
                                    <span>Masuk ke Dashboard</span>
                                    <svg class="w-4.5 h-4.5 text-white/90 absolute right-4.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <button type="button"
                                        x-show="hasRegisteredBiometric"
                                        x-cloak
                                        @click="loginWithBiometric()"
                                        :disabled="biometricLoading"
                                        title="Masuk dengan Sidik Jari / Biometrik"
                                        aria-label="Masuk dengan Sidik Jari / Biometrik"
                                        class="gomsn-btn-biometric">
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
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- LAYER 3: MOBILE FOOTER WITH INTEGRATED SWEEPING TEAL WAVES     -->
        <!-- ============================================================== -->
        <footer class="relative z-10 w-full select-none lg:hidden mt-auto shrink-0">
            <!-- Smooth Wave Transition Header -->
            <div class="w-full h-10 sm:h-12 overflow-hidden leading-none pointer-events-none -mb-[1px]">
                <svg class="w-full h-full block" viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="footerDarkWave" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#03262c" />
                            <stop offset="50%" stop-color="#043239" />
                            <stop offset="100%" stop-color="#06444a" />
                        </linearGradient>
                    </defs>
                    <!-- Soft Aqua Ribbon -->
                    <path d="M0,12 C360,65 720,70 1080,24 C1260,8 1370,10 1440,14 L1440,90 L0,90 Z" fill="rgba(94, 234, 212, 0.38)" />
                    <!-- Vibrant Teal Ribbon -->
                    <path d="M0,25 C370,75 720,78 1070,35 C1250,18 1365,16 1440,24 L1440,90 L0,90 Z" fill="rgba(20, 184, 166, 0.52)" />
                    <!-- Solid Dark Teal Base Wave -->
                    <path d="M0,38 C380,84 720,86 1060,46 C1240,26 1360,24 1440,32 L1440,90 L0,90 Z" fill="url(#footerDarkWave)" />
                </svg>
            </div>
            <!-- Solid Dark Teal Base with Centered Copyright -->
            <div class="bg-[#043239] pt-1 pb-3.5 sm:pb-4 px-4 text-center" style="padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));">
                <p class="text-[11px] sm:text-xs text-teal-100/90 font-normal tracking-wide">
                    &copy; {{ date('Y') }} PT. Media Solusi Network. All Rights Reserved.
                </p>
            </div>
        </footer>
    </div>
</x-layouts.app>
