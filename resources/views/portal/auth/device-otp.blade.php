<x-layouts.app title="Verifikasi OTP Perangkat Baru">
    <div class="max-w-lg mx-auto py-8">
        <div class="card-enterprise p-6 sm:p-8">
            <x-status-badge variant="warning" label="Device Binding §5.1 · OTP §6.1" />
            <h1 class="font-heading text-xl font-bold text-[#1E3A8A] mt-3">
                Verifikasi Perangkat Baru
            </h1>
            <p class="text-xs text-[#475569] mt-1">
                Akun Anda telah mencapai batas maksimal perangkat terdaftar. Masukkan 6 digit kode OTP (berlaku 5 menit, maks. 3 percobaan) untuk mengotorisasi perangkat <strong>{{ $device?->device_name ?? 'Perangkat Baru' }}</strong>.
            </p>

            @if (session('demo_otp_code'))
                <div class="mt-4 rounded-xl bg-blue-50 border border-[#BFDBFE] p-3 text-xs text-[#1E3A8A]">
                    <strong>Simulasi OTP Lingkungan Dev:</strong> Kode OTP Anda adalah
                    <span class="font-mono font-bold text-base px-2 py-0.5 bg-white rounded border border-[#BFDBFE]">{{ session('demo_otp_code') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('auth.device.otp.verify') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="otp_code" class="block text-xs font-semibold text-[#1E3A8A] mb-1">Kode OTP (6 Digit)</label>
                    <input
                        id="otp_code"
                        name="otp_code"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        required
                        placeholder="000000"
                        class="w-full rounded-xl border border-[#BFDBFE] px-4 py-3 text-center font-mono text-lg tracking-widest"
                    >
                </div>

                <button type="submit" class="btn-accent w-full">
                    Verifikasi &amp; Setujui Perangkat
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>

