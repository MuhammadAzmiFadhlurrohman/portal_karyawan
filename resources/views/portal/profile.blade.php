<x-layouts.app title="Profil Saya - Portal MSN">
    <div class="space-y-6 max-w-6xl mx-auto">
        <!-- Top Profile Banner Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="h-28 sm:h-32 w-full" style="background: linear-gradient(135deg, #155E63 0%, #17555B 45%, #1F2937 100%) !important;"></div>
            <div class="px-6 pb-6 pt-0 relative flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-12 sm:-mt-14">
                <div class="flex items-end gap-4">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#1F2937] text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-md border-4 border-white shrink-0">
                        {{ substr($user->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="min-w-0 pb-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                {{ $user->getRoleNames()->first() ? ucfirst($user->getRoleNames()->first()) : 'Staff' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">
                            {{ $user->employee_code }} &bull; {{ $user->division ?: 'Operasional' }} &bull; {{ $user->position ?: 'Karyawan' }}
                        </p>
                    </div>
                </div>

                @if (auth()->user() && auth()->user()->hasRole(['super_admin', 'admin']))
                <div class="flex items-center gap-2 pt-2 sm:pt-0">
                    <a href="{{ route('portal.sessions') }}" class="btn-secondary !text-xs !py-2 !px-3.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Sesi &amp; Perangkat</span>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Grid 2 Kolom: Data Kepegawaian & Form Update Profil -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Kolom Kiri: Informasi Kepegawaian (Read-Only) -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Data Kepegawaian</h2>
                            <p class="text-[11px] text-slate-500">Informasi terdaftar resmi pada sistem HRD MSN</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kode Karyawan (NIK Internal)</span>
                            <span class="font-mono font-bold text-slate-800 text-sm">{{ $user->employee_code }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nomor Induk Kependudukan (NIK)</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $user->nik ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Username Login</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $user->username }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Email Resmi</span>
                            <span class="font-semibold text-slate-800">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Divisi / Departemen</span>
                            <span class="font-semibold text-slate-800">{{ $user->division ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Jabatan / Posisi</span>
                            <span class="font-semibold text-slate-800">{{ $user->position ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Atasan Langsung (Supervisor)</span>
                            <span class="font-semibold text-slate-800">{{ $user->supervisor?->name ?? 'Pengajuan Mandiri / Direksi' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tanggal Bergabung</span>
                            <span class="font-semibold text-slate-800">{{ $user->join_date ? \Carbon\Carbon::parse($user->join_date)->format('d F Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Form Update Kontak, Rekening Bank, & Sandi -->
            <div class="lg:col-span-7 space-y-6">
                <form method="POST" action="{{ route('portal.profile.update') }}" class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Kontak & Rekening Bank -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Kontak &amp; Rekening Pencairan</h2>
                                <p class="text-[11px] text-slate-500">Diperlukan untuk notifikasi dan pencairan kasbon / reimbursement oleh Finance</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Bank</label>
                                <input type="text" name="bank" value="{{ old('bank', $user->bank) }}" placeholder="Contoh: BCA, Mandiri, BRI" class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nomor Rekening</label>
                                <input type="text" name="no_rekening" value="{{ old('no_rekening', $user->no_rekening) }}" placeholder="Contoh: 1234567890" class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium font-mono">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Nama Pemilik Rekening (Atas Nama)</label>
                                <input type="text" name="atas_nama" value="{{ old('atas_nama', $user->atas_nama) }}" placeholder="Nama persis sesuai buku tabungan" class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Ganti Password (Opsional) -->
                    <div class="space-y-4 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Ubah Password Akun</h2>
                                <p class="text-[11px] text-slate-500">Kosongkan kolom di bawah jika Anda tidak ingin mengubah password</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Password Saat Ini</label>
                                <input type="password" name="current_password" placeholder="Masukkan password lama untuk konfirmasi..." class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Password Baru (Min. 8 Karakter)</label>
                                <input type="password" name="new_password" placeholder="Password baru..." class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="new_password_confirmation" placeholder="Ulangi password baru..." class="w-full rounded-xl border border-slate-300 py-2.5 px-3 focus:ring-teal-700 focus:border-teal-700 font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" 
                                style="background: linear-gradient(135deg, #155E63 0%, #17555B 50%, #1F2937 100%) !important;"
                                class="hover:opacity-90 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
