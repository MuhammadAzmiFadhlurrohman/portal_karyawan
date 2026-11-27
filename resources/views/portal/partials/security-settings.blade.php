@php
    $secUser = $user ?? auth()->user();
    $secConfig = [
        'optionsUrl' => route('portal.profile.webauthn.options', [], false),
        'registerUrl' => route('portal.profile.webauthn.register', [], false),
        'csrfToken' => csrf_token(),
    ];
@endphp

<script>
    window.__gomsnSecuritySettingsConfig = @json($secConfig);

    function gomsnSecuritySettings() {
        const cfg = window.__gomsnSecuritySettingsConfig || {};
        return {
            showCurrentPass: false,
            showNewPass: false,
            showConfirmPass: false,
            biometricLoading: false,
            isIphone: /iPhone/i.test(navigator.userAgent || ''),
            isApple: /iPhone|iPad|Macintosh/i.test(navigator.userAgent || ''),

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

            removeLocalBiometricCred(credId) {
                try {
                    const list = JSON.parse(localStorage.getItem('gomsn_biometric_creds') || '[]');
                    localStorage.setItem('gomsn_biometric_creds', JSON.stringify(list.filter(id => id !== credId)));
                } catch (e) {}
            },

            async registerBiometric() {
                if (!window.PublicKeyCredential) {
                    Swal.fire({ icon: 'warning', title: 'Tidak Didukung', text: 'Browser atau perangkat ini belum mendukung login biometrik WebAuthn / Passkey.' });
                    return;
                }
                if (!window.isSecureContext) {
                    Swal.fire({ icon: 'warning', title: 'Koneksi Harus HTTPS', text: 'Fitur sidik jari/Face ID hanya berjalan pada koneksi HTTPS yang aman.' });
                    return;
                }
                this.biometricLoading = true;
                try {
                    const csrfMeta = document.querySelector('meta[name=csrf-token]');
                    const csrf = (csrfMeta && csrfMeta.content) || cfg.csrfToken || '';
                    const optRes = await fetch(cfg.optionsUrl || '/portal/profile/webauthn/options', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({})
                    });
                    const opts = await optRes.json();
                    if (!optRes.ok) throw new Error(opts.message || 'Gagal menyiapkan pendaftaran biometrik.');

                    const publicKey = {
                        ...opts,
                        challenge: this.b64UrlToUint8(opts.challenge),
                        user: {
                            ...opts.user,
                            id: this.b64UrlToUint8(opts.user.id)
                        }
                    };

                    if (opts.excludeCredentials && opts.excludeCredentials.length > 0) {
                        publicKey.excludeCredentials = opts.excludeCredentials.map(c => ({
                            ...c,
                            id: this.b64UrlToUint8(c.id)
                        }));
                    } else {
                        delete publicKey.excludeCredentials;
                    }

                    const credential = await navigator.credentials.create({ publicKey });
                    if (!credential) throw new Error('Pendaftaran dibatalkan.');

                    const credentialId = this.bufToB64Url(credential.rawId);
                    const defaultDeviceName = this.isIphone
                        ? 'iPhone (Face ID)'
                        : (this.isApple ? 'Apple Device (Face ID / Touch ID)' : '');

                    const payload = {
                        credential_id: credentialId,
                        device_name: defaultDeviceName,
                        client_data_json: this.bufToB64Url(credential.response.clientDataJSON),
                        attestation_object: this.bufToB64Url(credential.response.attestationObject),
                        public_key_spki: typeof credential.response.getPublicKey === 'function' && credential.response.getPublicKey()
                            ? this.bufToB64Url(credential.response.getPublicKey())
                            : null,
                        algorithm: typeof credential.response.getPublicKeyAlgorithm === 'function'
                            ? credential.response.getPublicKeyAlgorithm()
                            : -7,
                        transports: typeof credential.response.getTransports === 'function'
                            ? credential.response.getTransports()
                            : []
                    };

                    const saveRes = await fetch(cfg.registerUrl || '/portal/profile/webauthn/register', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify(payload)
                    });
                    const saveJson = await saveRes.json();
                    if (!saveRes.ok) throw new Error(saveJson.message || 'Gagal menyimpan kredensial biometrik.');

                    try {
                        const existing = JSON.parse(localStorage.getItem('gomsn_biometric_creds') || '[]');
                        if (!existing.includes(credentialId)) existing.push(credentialId);
                        localStorage.setItem('gomsn_biometric_creds', JSON.stringify(existing));
                    } catch (e) {}

                    await Swal.fire({
                        icon: 'success',
                        title: this.isIphone ? 'Face ID Aktif!' : 'Biometrik Aktif!',
                        text: this.isIphone
                            ? 'Face ID iPhone Anda berhasil didaftarkan. Login berikutnya cukup menatap layar tanpa mengetik password.'
                            : 'Sidik jari / Face ID / Windows Hello berhasil didaftarkan untuk login cepat.',
                        confirmButtonColor: '#155E63'
                    });
                    window.location.reload();
                } catch (err) {
                    let msg = err.message || 'Terjadi kesalahan.';
                    if (err.name === 'NotAllowedError') {
                        msg = this.isIphone
                            ? 'Verifikasi Face ID dibatalkan atau izin biometrik tidak diberikan.'
                            : 'Proses sensor biometrik dibatalkan atau waktu verifikasi habis.';
                    } else if (err.name === 'InvalidStateError') {
                        msg = 'Perangkat/biometrik ini sudah terdaftar pada akun Anda.';
                    }
                    Swal.fire({ icon: 'error', title: 'Gagal Mengaktifkan', text: msg, confirmButtonColor: '#155E63' });
                } finally {
                    this.biometricLoading = false;
                }
            }
        };
    }
</script>

<div id="keamanan" class="grid grid-cols-1 lg:grid-cols-12 gap-5" x-data="gomsnSecuritySettings()">
    <!-- 1. Login Biometrik & Passkey (WebAuthn FIDO2) -->
    <div class="lg:col-span-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-4">
        <div class="flex items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-[#155E63] flex items-center justify-center shrink-0 shadow-2xs">
                    <template x-if="isApple || isIphone">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M9 9h.01M15 9h.01M10 13h4M9 16c1.5 1.5 4.5 1.5 6 0"/>
                        </svg>
                    </template>
                    <template x-if="!isApple && !isIphone">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                        </svg>
                    </template>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900" x-text="isIphone ? 'Login Face ID & Passkey' : 'Login Biometrik & Passkey'"></h2>
                    <p class="text-[11px] text-slate-500" x-text="isIphone ? 'Face ID iPhone (iOS Safari) & Passkey' : 'Sidik jari, Face ID, atau Windows Hello'"></p>
                </div>
            </div>
            @if ($secUser->webauthnCredentials->isNotEmpty())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $secUser->webauthnCredentials->count() }} Terdaftar
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold shrink-0">
                    Belum Aktif
                </span>
            @endif
        </div>

        @if ($secUser->webauthnCredentials->isNotEmpty())
            <div class="space-y-2.5 text-xs">
                @foreach ($secUser->webauthnCredentials as $cred)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-white border border-slate-200/80 text-[#155E63] flex items-center justify-center shrink-0">
                                @if (str_contains(strtolower($cred->device_name), 'iphone') || str_contains(strtolower($cred->device_name), 'face id'))
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M9 9h.01M15 9h.01M10 13h4M9 16c1.5 1.5 4.5 1.5 6 0"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate">{{ $cred->device_name }}</p>
                                <p class="text-[10.5px] text-slate-500 truncate">
                                    @if ($cred->last_used_at)
                                        Terakhir login: {{ $cred->last_used_at->diffForHumans() }}
                                    @else
                                        Terdaftar {{ $cred->created_at?->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <form method="POST"
                              action="{{ route('portal.profile.webauthn.destroy', $cred) }}"
                              @submit="removeLocalBiometricCred('{{ $cred->credential_id }}')"
                              data-confirm="Hapus perangkat biometrik '{{ $cred->device_name }}' dari akun Anda?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    title="Hapus Biometrik Perangkat Ini"
                                    class="p-2 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl bg-teal-50/60 border border-teal-100 p-3.5 text-[11.5px] text-teal-900 leading-relaxed">
                <span x-show="isIphone">Aktifkan Face ID di iPhone Anda agar login berikutnya cukup menatap layar HP dalam &lt; 1 detik tanpa mengetik password.</span>
                <span x-show="!isIphone">Aktifkan fitur ini sekali di HP atau laptop Anda agar login berikutnya cukup menempelkan <strong>Sidik Jari / Face ID / Windows Hello</strong> dalam &lt; 1 detik tanpa mengetik password.</span>
            </div>
        @endif

        <button type="button"
                @click="registerBiometric()"
                :disabled="biometricLoading"
                class="w-full py-2.5 px-4 rounded-xl border border-teal-500/30 bg-teal-50/80 hover:bg-teal-600 text-teal-700 hover:text-white font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-60">
            <template x-if="!biometricLoading">
                <svg x-show="isApple || isIphone" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M9 9h.01M15 9h.01M10 13h4M9 16c1.5 1.5 4.5 1.5 6 0"/>
                </svg>
            </template>
            <template x-if="!biometricLoading">
                <svg x-show="!isApple && !isIphone" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                </svg>
            </template>
            <svg x-show="biometricLoading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span x-text="biometricLoading ? (isIphone ? 'Menatap Layar / Verifikasi Face ID...' : 'Menunggu Verifikasi Sensor...') : (isIphone ? 'Aktifkan Face ID di iPhone Ini' : (isApple ? 'Aktifkan Face ID / Touch ID di Perangkat Ini' : 'Aktifkan Sidik Jari / Biometrik di Perangkat Ini'))"></span>
        </button>
    </div>

    <!-- 2. Keamanan & Ubah Password -->
    <div class="lg:col-span-7 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-5">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">Keamanan &amp; Ubah Password</h2>
                <p class="text-[11px] text-slate-500">Perbarui password akun Anda secara berkala untuk menjaga keamanan portal</p>
            </div>
        </div>

        @if ($errors->hasAny(['current_password', 'new_password', 'new_password_confirmation']))
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700 space-y-0.5">
                @foreach (['current_password', 'new_password', 'new_password_confirmation'] as $field)
                    @error($field)
                        <div>{{ $message }}</div>
                    @enderror
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('portal.settings.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Password Saat Ini -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                    <div class="relative">
                        <input :type="showCurrentPass ? 'text' : 'password'"
                               name="current_password"
                               required
                               placeholder="Masukkan password lama untuk konfirmasi..."
                               class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 font-medium text-slate-800 transition">
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
                               required
                               minlength="8"
                               placeholder="Password baru..."
                               class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 font-medium text-slate-800 transition">
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
                               required
                               minlength="8"
                               placeholder="Ulangi password baru..."
                               class="w-full rounded-xl border border-slate-300 bg-slate-50/40 focus:bg-white py-2.5 pl-3.5 pr-10 focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 font-medium text-slate-800 transition">
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

            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-[11px] text-slate-400">
                    Perubahan password tercatat otomatis pada sistem keamanan Portal MSN.
                </p>
                <button type="submit"
                        style="background: var(--gomsn-active-bg) !important;"
                        class="w-full sm:w-auto justify-center hover:opacity-95 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Password Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>
