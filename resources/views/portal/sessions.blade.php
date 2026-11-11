<x-layouts.app title="Sesi Aktif, Device Binding & Delegasi">
    <div class="space-y-8">
        <!-- Section 1: Active User Sessions & Force Logout (§6.1) -->
        <section class="card-enterprise p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <x-status-badge variant="info" label="Manajemen Sesi Multi-Perangkat · §6.1" />
                    <h1 class="font-heading text-xl font-bold text-[#1E3A8A] mt-2">
                        Daftar Sesi Login (<code class="font-mono">user_sessions</code>)
                    </h1>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#BFDBFE] text-[#475569] uppercase">
                            <th class="py-2.5 px-3">Pengguna</th>
                            <th class="py-2.5 px-3">Perangkat</th>
                            <th class="py-2.5 px-3">IP Address</th>
                            <th class="py-2.5 px-3">Aktivitas Terakhir</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Tindakan Force Logout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFDBFE]/60">
                        @foreach ($sessions as $s)
                            <tr>
                                <td class="py-3 px-3 font-semibold text-[#1E3A8A]">
                                    {{ $s->user?->name }}
                                    <span class="block font-mono text-[11px] text-[#475569]">{{ $s->user?->employee_code }}</span>
                                </td>
                                <td class="py-3 px-3">{{ $s->device_name }}</td>
                                <td class="py-3 px-3 font-mono">{{ $s->ip_address }}</td>
                                <td class="py-3 px-3 font-mono">{{ $s->last_activity_at?->format('d M Y H:i:s') }}</td>
                                <td class="py-3 px-3">
                                    @if ($s->revoked_at)
                                        <x-status-badge variant="neutral" label="Dihentikan ({{ $s->revoke_reason }})" />
                                    @else
                                        <x-status-badge variant="success" label="Aktif" />
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if (! $s->revoked_at)
                                        <form method="POST" action="{{ route('portal.sessions.revoke', $s) }}" class="inline-flex items-center gap-2">
                                            @csrf
                                            <input
                                                type="text"
                                                name="reason"
                                                required
                                                placeholder="Alasan force logout..."
                                                class="rounded-lg border border-[#BFDBFE] px-2.5 py-1.5 text-xs"
                                            >
                                            <button type="submit" class="btn-danger">
                                                Force Logout
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section 2: Registered Devices / Device Binding (§5.1) -->
        <section class="card-enterprise p-6">
            <x-status-badge variant="info" label="Device Binding Maks. 2 Perangkat · §5.1" />
            <h2 class="font-heading text-xl font-bold text-[#1E3A8A] mt-2 mb-4">
                Perangkat Terdaftar (<code class="font-mono">user_devices</code>)
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#BFDBFE] text-[#475569] uppercase">
                            <th class="py-2.5 px-3">Karyawan</th>
                            <th class="py-2.5 px-3">Nama Perangkat</th>
                            <th class="py-2.5 px-3">Fingerprint</th>
                            <th class="py-2.5 px-3">Status</th>
                            @if ($canManageAll)
                                <th class="py-2.5 px-3 text-right">Kontrol Admin/HRD</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFDBFE]/60">
                        @foreach ($devices as $d)
                            <tr>
                                <td class="py-3 px-3 font-semibold text-[#1E3A8A]">{{ $d->user?->name }}</td>
                                <td class="py-3 px-3">{{ $d->device_name }} ({{ $d->platform }})</td>
                                <td class="py-3 px-3 font-mono text-[11px] text-[#475569]">{{ \Illuminate\Support\Str::limit($d->device_fingerprint, 20) }}</td>
                                <td class="py-3 px-3">
                                    <x-status-badge
                                        :variant="$d->status === 'approved' ? 'success' : ($d->status === 'pending' ? 'warning' : 'danger')"
                                        :label="strtoupper($d->status)"
                                    />
                                </td>
                                @if ($canManageAll)
                                    <td class="py-3 px-3 text-right">
                                        <form method="POST" action="{{ route('portal.devices.approve', $d) }}" class="inline-flex items-center gap-2">
                                            @csrf
                                            <select name="status" class="rounded-lg border border-[#BFDBFE] px-2 py-1.5 text-xs">
                                                <option value="approved" @selected($d->status === 'approved')>Setujui</option>
                                                <option value="revoked" @selected($d->status === 'revoked')>Cabut Akses</option>
                                            </select>
                                            <input type="text" name="reason" required placeholder="Alasan audit..." class="rounded-lg border border-[#BFDBFE] px-2.5 py-1.5 text-xs">
                                            <button type="submit" class="btn-secondary !py-1.5 !px-3 text-xs">Simpan</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section 3: Approval Delegations (§4.3) -->
        <section class="card-enterprise p-6">
            <x-status-badge variant="info" label="Hierarki & Delegasi Atasan · §4.3" />
            <h2 class="font-heading text-xl font-bold text-[#1E3A8A] mt-2 mb-4">
                Delegasi Persetujuan (<code class="font-mono">approval_delegations</code>)
            </h2>

            <form method="POST" action="{{ route('portal.delegations.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6 bg-blue-50/50 p-4 rounded-xl border border-[#BFDBFE]">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Pemberi Delegasi</label>
                    <select name="delegator_id" class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected($u->id === auth()->id())>{{ $u->name }} ({{ $u->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Penerima Delegasi</label>
                    <select name="delegate_id" class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Modul</label>
                    <select name="module" class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                        <option value="all">Semua Modul</option>
                        <option value="attendance">Absensi &amp; Cuti</option>
                        <option value="ticketing">Tiketing</option>
                        <option value="gomsn">GOMSN Operasional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Mulai</label>
                    <input type="date" name="start_date" required value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Selesai</label>
                    <input type="date" name="end_date" required value="{{ now()->addDays(7)->toDateString() }}" class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#1E3A8A] mb-1">Alasan</label>
                    <div class="flex gap-2">
                        <input type="text" name="reason" required placeholder="Cuti / Dinas..." class="w-full rounded-lg border border-[#BFDBFE] bg-white px-2.5 py-2 text-xs">
                        <button type="submit" class="btn-primary !py-1.5 !px-3 text-xs">Tambah</button>
                    </div>
                </div>
            </form>

            <div class="space-y-2">
                @foreach ($delegations as $del)
                    <div class="rounded-xl border border-[#BFDBFE] px-4 py-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div>
                            <strong class="text-[#1E3A8A]">{{ $del->delegator?->name }}</strong>
                            mendelegasikan persetujuan modul <span class="font-mono font-bold">{{ strtoupper($del->module) }}</span> kepada
                            <strong class="text-emerald-800">{{ $del->delegate?->name }}</strong>
                            ({{ $del->start_date?->format('d M Y') }} s.d. {{ $del->end_date?->format('d M Y') }})
                        </div>
                        <span class="text-[#475569]">Alasan: {{ $del->reason }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>

