<x-layouts.app title="Konfigurasi Sistem (system_settings)">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-status-badge variant="info" label="Single Source of Truth · §6.3 & AC-09" />
            <h1 class="font-heading text-2xl font-bold text-[#1E3A8A] mt-2">
                Konfigurasi Parameter Sistem (<code class="font-mono">system_settings</code>)
            </h1>
            <p class="text-xs text-[#475569] mt-1">
                Setiap perubahan parameter menyimpan tanggal mulai berlaku (<code class="font-mono">effective_from</code>) dan alasan wajib ke <code class="font-mono">audit_logs</code> agar periode historis yang sudah ditutup tidak berubah (AC-09).
            </p>
        </div>
    </div>

    <div class="space-y-4">
        @foreach ($currentSettings as $setting)
            <div class="card-enterprise p-5">
                <form method="POST" action="{{ route('portal.settings.update') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    @csrf
                    <input type="hidden" name="key" value="{{ $setting->key }}">

                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-blue-100 px-2 py-0.5 font-mono text-[11px] font-bold text-[#1E40AF]">{{ $setting->group }}</span>
                            <span class="font-mono text-xs font-semibold text-slate-700">{{ $setting->key }}</span>
                        </div>
                        <label class="block font-heading text-sm font-bold text-[#1E3A8A] mt-1">{{ $setting->label }}</label>
                        <p class="text-xs text-[#475569] mt-0.5">{{ $setting->description }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Nilai ({{ $setting->type }})</label>
                        <input
                            type="text"
                            name="value"
                            required
                            value="{{ $setting->value }}"
                            class="w-full rounded-xl border border-[#BFDBFE] bg-white px-3 py-2 text-sm font-mono text-slate-900"
                        >
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Berlaku Mulai</label>
                        <input
                            type="datetime-local"
                            name="effective_from"
                            required
                            value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-xl border border-[#BFDBFE] bg-white px-3 py-2 text-xs font-mono text-slate-900"
                        >
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Alasan Perubahan (Wajib Audit)</label>
                        <input
                            type="text"
                            name="change_reason"
                            required
                            placeholder="Mis: Penyesuaian kebijakan HRD Q4"
                            class="w-full rounded-xl border border-[#BFDBFE] bg-white px-3 py-2 text-xs text-slate-900"
                        >
                    </div>

                    <div class="lg:col-span-1">
                        <button type="submit" class="btn-primary w-full !px-3">
                            Simpan
                        </button>
                    </div>
                </form>

                @php
                    $revisions = $historyByKey->get($setting->key, collect());
                @endphp
                @if ($revisions->count() > 1)
                    <div class="mt-3 pt-3 border-t border-[#BFDBFE]/60 text-xs text-[#475569]">
                        <span class="font-semibold text-[#1E3A8A]">Riwayat Tarif/Nilai ({{ $revisions->count() }} versi):</span>
                        <div class="mt-1.5 flex flex-wrap gap-2">
                            @foreach ($revisions->take(4) as $rev)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-[11px]">
                                    <strong>{{ $rev->value }}</strong>
                                    <span>· berlaku {{ $rev->effective_from?->format('d M Y H:i') }}</span>
                                    <span class="text-slate-500">({{ $rev->change_reason }})</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-layouts.app>

