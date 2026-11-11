<x-layouts.app title="Jejak Audit Global (audit_logs)">
    <div class="card-enterprise p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div>
                <x-status-badge variant="info" label="Immutable Append-Only · §4.3, §9.2, §10.2" />
                <h1 class="font-heading text-2xl font-bold text-[#1E3A8A] mt-2">
                    Jejak Audit Global (<code class="font-mono">audit_logs</code>)
                </h1>
                <p class="text-xs text-[#475569] mt-1">
                    Catatan ini dilindungi pada level model agar tidak dapat diubah maupun dihapus dari antarmuka (retensi minimal 2 tahun).
                </p>
            </div>

            <form method="GET" action="{{ route('portal.audit') }}" class="flex items-center gap-2">
                <input
                    type="text"
                    name="action"
                    value="{{ request('action') }}"
                    placeholder="Filter aksi (mis: auth., system_setting.)"
                    class="rounded-xl border border-[#BFDBFE] bg-white px-3.5 py-2 text-xs"
                >
                <button type="submit" class="btn-secondary !py-2 !px-3.5 text-xs">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#BFDBFE] text-[#475569] uppercase">
                        <th class="py-2.5 px-3">Waktu (UTC)</th>
                        <th class="py-2.5 px-3">Pelaku</th>
                        <th class="py-2.5 px-3">Aksi</th>
                        <th class="py-2.5 px-3">Perubahan Nilai (Lama → Baru)</th>
                        <th class="py-2.5 px-3">Alasan &amp; Request ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BFDBFE]/60">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="py-3 px-3 font-mono whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-3 font-semibold text-[#1E3A8A]">
                                {{ $log->user?->name ?? 'System / Guest' }}
                                <span class="block font-mono text-[11px] text-[#475569]">{{ $log->ip }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="rounded bg-blue-50 border border-[#BFDBFE] px-2 py-0.5 font-mono font-semibold text-[#1E40AF]">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] max-w-md">
                                @if ($log->old_values)
                                    <div class="text-red-700 truncate">Old: {{ json_encode($log->old_values) }}</div>
                                @endif
                                @if ($log->new_values)
                                    <div class="text-emerald-800 truncate">New: {{ json_encode($log->new_values) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="block text-slate-800">{{ $log->reason ?? '—' }}</span>
                                <span class="block font-mono text-[10px] text-slate-400">Req: {{ $log->request_id ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6">
                                <x-empty-state title="Belum ada log audit" description="Aktivitas autentikasi, perubahan konfigurasi, dan override akan tercatat otomatis di sini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts.app>

