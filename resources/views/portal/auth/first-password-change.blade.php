<x-layouts.app title="Wajib Ganti Password Pertama Kali">
    <div class="max-w-lg mx-auto py-8">
        <div class="card-enterprise p-6 sm:p-8">
            <x-status-badge variant="warning" label="Kebijakan Keamanan §6.1" />
            <h1 class="font-heading text-xl font-bold text-[#1E3A8A] mt-3">
                Perbarui Password Anda
            </h1>
            <p class="text-xs text-[#475569] mt-1">
                Sesuai kebijakan keamanan PT MSN (§6.1), Anda wajib mengganti password saat login pertama kali dengan minimal 10 karakter (kombinasi huruf besar, huruf kecil, dan angka).
            </p>

            <form method="POST" action="{{ route('password.first-change.update') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-[#1E3A8A] mb-1">Password Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" required class="w-full rounded-xl border border-[#BFDBFE] px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#1E3A8A] mb-1">Password Baru (Min. 10 Karakter)</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-xl border border-[#BFDBFE] px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-[#1E3A8A] mb-1">Konfirmasi Password Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-[#BFDBFE] px-3.5 py-2.5 text-sm">
                </div>

                <button type="submit" class="btn-primary w-full">
                    Simpan Password Baru &amp; Lanjutkan
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>

