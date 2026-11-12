<x-layouts.app title="Laporan Absensi Karyawan">
    <div class="space-y-5 w-full pb-12"
         x-data="{
            search: '{{ addslashes($searchTerm) }}',
            editModalOpen: false,
            photoModalOpen: false,
            activePhoto: '',
            activePhotoName: '',
            editData: {
                user_id: '',
                employee_name: '',
                employee_code: '',
                date: '{{ $targetDate }}',
                status: 'present',
                clock_in: '',
                clock_out: '',
                notes: ''
            },
            openEditModal(row) {
                this.editData = {
                    user_id: row.user.id,
                    employee_name: row.user.name,
                    employee_code: row.user.employee_code,
                    date: '{{ $targetDate }}',
                    status: row.status,
                    clock_in: row.clock_in || '',
                    clock_out: row.clock_out || '',
                    notes: row.attendance ? (row.attendance.notes || '') : ''
                };
                this.editModalOpen = true;
            },
            openPhotoModal(url, name) {
                this.activePhoto = url;
                this.activePhotoName = name;
                this.photoModalOpen = true;
            },
            async quickUpdateStatus(userId, status) {
                try {
                    const token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content');
                    const res = await fetch('{{ route('attendance.reports.quick-status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            date: '{{ $targetDate }}',
                            status: status
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal mengubah status.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan koneksi saat mengubah status.');
                }
            }
         }">

        <!-- Top Header Sesuai Contoh Gambar -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight leading-tight">Laporan Absensi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Riwayat kehadiran karyawan per hari</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('attendance.reports.export', ['date' => $targetDate]) }}" 
                   class="inline-flex items-center gap-1.5 px-4.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export</span>
                </a>
            </div>
        </div>

        <!-- Filter Card Tanggal Sesuai Contoh Gambar -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-5 shadow-xs space-y-3">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider"># TANGGAL</label>
            <form method="GET" action="{{ route('attendance.reports') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <input type="date" 
                           name="date" 
                           value="{{ $targetDate }}" 
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-800 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition shadow-2xs">
                </div>

                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Tampilkan</span>
                </button>

                <a href="{{ route('attendance.reports') }}" 
                   class="inline-flex items-center justify-center gap-1.5 px-4.5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Reset</span>
                </a>
            </form>
        </div>

        <!-- Banner Indikator Tanggal Sesuai Gambar -->
        <div class="rounded-2xl bg-blue-50 border border-blue-200/80 p-3.5 sm:px-4 text-blue-900 text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-2xs">
            <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span>Menampilkan data tanggal: <strong class="text-blue-950 font-bold">{{ $dateFormatted }}</strong></span>
        </div>

        <!-- Flash Status Notification -->
        @if (session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 text-xs sm:text-sm font-semibold flex items-center gap-2.5 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Tabel Data Kehadiran Sesuai Contoh Gambar -->
        <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Card & Search Input -->
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <h2 class="font-bold text-sm text-slate-800">Data Kehadiran</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $totalRecords }} record
                    </span>
                </div>

                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari nama karyawan..." 
                           class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition">
                </div>
            </div>

            <!-- Tabel Data Kehadiran -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider text-[11px] bg-slate-50/70 border-b border-slate-100">
                            <th class="py-3.5 px-4 text-center w-12">NO</th>
                            <th class="py-3.5 px-4">KARYAWAN</th>
                            <th class="py-3.5 px-4 min-w-[190px]">JAM KERJA</th>
                            <th class="py-3.5 px-4 text-center">FOTO ABSEN</th>
                            <th class="py-3.5 px-4">LOKASI</th>
                            <th class="py-3.5 px-4 text-center min-w-[160px]">STATUS</th>
                            <th class="py-3.5 px-4">KETERANGAN</th>
                            <th class="py-3.5 px-4 text-center w-16">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rows as $idx => $row)
                        @php
                            $user = $row['user'];
                            $att = $row['attendance'];
                            $isAbsent = ($row['status'] === 'absent');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors"
                            x-show="!search || '{{ strtolower(addslashes($user->name . ' ' . $user->employee_code . ' ' . ($user->division ?? ''))) }}'.includes(search.toLowerCase())">
                            
                            <!-- NO -->
                            <td class="py-4 px-4 text-center font-bold text-slate-700">
                                <span class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center mx-auto text-[11px] text-slate-600">
                                    {{ $idx + 1 }}
                                </span>
                            </td>

                            <!-- KARYAWAN -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" 
                                                 alt="" 
                                                 class="w-10 h-10 rounded-2xl object-cover border border-slate-200 shadow-2xs" 
                                                 onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                            <div style="display: none;" class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                                {{ $user->initials }}
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#18C4C4] to-[#155E63] text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                                {{ $user->initials }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 text-xs block leading-tight">{{ $user->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">{{ $user->employee_code }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- JAM KERJA (Box Persis Sesuai Gambar) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-2.5 text-[11px] space-y-1.5 min-w-[170px] shadow-2xs">
                                    @if (! $isAbsent && $row['clock_in'])
                                        <div class="flex items-center justify-between text-slate-700">
                                            <span class="flex items-center gap-1.5 text-slate-500 font-medium">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14"/></svg>
                                                <span>Masuk</span>
                                            </span>
                                            <strong class="font-mono text-slate-800 text-xs">{{ $row['clock_in'] }}</strong>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-700">
                                            <span class="flex items-center gap-1.5 text-slate-500 font-medium">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                                                <span>Pulang</span>
                                            </span>
                                            <span class="font-mono text-slate-600 text-xs">{{ $row['clock_out'] ?: '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-slate-600">
                                            <span class="flex items-center gap-1.5 text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Total</span>
                                            </span>
                                            <span class="font-semibold text-blue-600 text-xs">{{ $row['duration'] ?: '-' }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between text-slate-400">
                                            <span>Masuk</span>
                                            <span class="font-mono">-</span>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-400">
                                            <span>Pulang</span>
                                            <span class="font-mono">-</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 pt-1 border-t border-slate-200/60 text-rose-600 font-bold text-[10px]">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span>Status: Tidak Absen</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- FOTO ABSEN -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if ($row['photo_path'])
                                    <div class="inline-block relative group cursor-pointer"
                                         @click="openPhotoModal('/storage/{{ $row['photo_path'] }}', '{{ addslashes($user->name) }}')">
                                        <img src="/storage/{{ $row['photo_path'] }}" 
                                             alt="Selfie" 
                                             class="w-12 h-12 object-cover rounded-xl border border-slate-200 shadow-2xs group-hover:scale-105 transition">
                                        <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-[9px] font-bold">
                                            Lihat
                                        </div>
                                    </div>
                                @else
                                    <div class="w-14 h-12 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-400 flex flex-col items-center justify-center text-[9px] mx-auto p-1 leading-tight">
                                        <span>Tidak ada</span>
                                        <span>foto</span>
                                    </div>
                                @endif
                            </td>

                            <!-- LOKASI -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if ($row['distance'])
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ $row['distance'] }}
                                        </span>
                                        @if ($row['map_url'])
                                            <a href="{{ $row['map_url'] }}" 
                                               target="_blank" 
                                               class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                                <span>📍 Lihat Maps</span>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">— tidak ada</span>
                                @endif
                            </td>

                            <!-- STATUS (Dropdown Cepat & Badge Sisa Cuti) -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center gap-1.5">
                                    <!-- Dropdown Ubah Status Cepat -->
                                    <div class="relative">
                                        <select @change="quickUpdateStatus({{ $user->id }}, $event.target.value)"
                                                class="text-xs font-bold rounded-full px-3 py-1 border shadow-2xs cursor-pointer outline-none appearance-none pr-6 text-center
                                                {{ $row['status'] === 'present' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 
                                                  ($row['status'] === 'late' ? 'bg-amber-50 text-amber-700 border-amber-300' : 
                                                  ($row['status'] === 'absent' ? 'bg-rose-50 text-rose-700 border-rose-300' : 
                                                  ($row['status'] === 'leave' ? 'bg-teal-50 text-teal-700 border-teal-300' : 
                                                  ($row['status'] === 'sick' ? 'bg-yellow-50 text-yellow-800 border-yellow-300' : 'bg-blue-50 text-blue-700 border-blue-300')))) }}">
                                            <option value="present" @selected($row['status'] === 'present')>✓ Hadir</option>
                                            <option value="late" @selected($row['status'] === 'late')>⏰ Terlambat</option>
                                            <option value="absent" @selected($row['status'] === 'absent')>✕ Alpa</option>
                                            <option value="leave" @selected($row['status'] === 'leave')>🌴 Cuti</option>
                                            <option value="sick" @selected($row['status'] === 'sick')>💊 Sakit</option>
                                            <option value="special" @selected($row['status'] === 'special')>📄 Izin</option>
                                        </select>
                                        <span class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-[9px]">▼</span>
                                    </div>

                                    <!-- Sub-Badges (Sisa Cuti & Cuti 1/3 Alpa) -->
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        @if ($row['alpa_cuti_label'])
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span>🌴</span>
                                                <span>{{ $row['alpa_cuti_label'] }}</span>
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">
                                            <span>🌴</span>
                                            <span>Sisa Cuti: {{ $row['remaining_leave'] }} hari</span>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- KETERANGAN -->
                            <td class="py-4 px-4 text-xs max-w-xs">
                                @if ($isAbsent)
                                    <span class="text-rose-600 font-semibold flex items-center gap-1">
                                        <span>⚠</span>
                                        <span>Tidak melakukan absensi</span>
                                    </span>
                                @elseif ($row['notes'] && $row['notes'] !== '-')
                                    <span class="text-slate-700">{{ $row['notes'] }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- AKSI EDIT MODAL (Pencil Button Persis Gambar) -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($row) }})"
                                        title="Edit Jam Kerja & Status Absensi"
                                        class="w-8 h-8 rounded-xl border border-blue-200 bg-blue-50/80 hover:bg-blue-100 text-blue-600 hover:text-blue-800 inline-flex items-center justify-center shadow-2xs transition active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada data karyawan aktif untuk ditampilkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL EDIT ABSENSI KARYAWAN (ROLE ADMIN)                       -->
        <!-- ============================================================== -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.stop class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all my-8">
                <!-- Modal Header -->
                <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white leading-tight">Edit Absensi Karyawan</h3>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-blue-100">
                                <span class="font-semibold" x-text="editData.employee_name"></span>
                                <span>•</span>
                                <span class="font-mono" x-text="editData.employee_code"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold text-lg leading-none transition cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form method="POST" action="{{ route('attendance.reports.update') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" :value="editData.user_id">
                    <input type="hidden" name="date" :value="editData.date">

                    <!-- Tanggal Info -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between">
                        <span class="text-slate-500">Tanggal Absensi:</span>
                        <strong class="font-mono text-slate-800" x-text="editData.date"></strong>
                    </div>

                    <!-- Status Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Status Kehadiran <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="editData.status" required
                                class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white font-medium text-slate-800 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-2xs">
                            <option value="present">Hadir (Tepat Waktu)</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Alpa (Tidak Absen)</option>
                            <option value="leave">Cuti Tahunan</option>
                            <option value="sick">Cuti Sakit</option>
                            <option value="special">Izin Khusus</option>
                        </select>
                    </div>

                    <!-- Jam Masuk & Jam Pulang -->
                    <div class="grid grid-cols-2 gap-3" x-show="editData.status !== 'absent'">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Masuk (HH:mm:ss)</label>
                            <input type="time" 
                                   name="clock_in" 
                                   step="1"
                                   x-model="editData.clock_in" 
                                   class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white text-slate-800 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Pulang (HH:mm:ss)</label>
                            <input type="time" 
                                   name="clock_out" 
                                   step="1"
                                   x-model="editData.clock_out" 
                                   class="w-full text-xs rounded-xl border-slate-300 py-2.5 px-3 bg-white text-slate-800 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-2xs">
                        </div>
                    </div>

                    <!-- Keterangan / Alasan Override -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan / Keterangan Penyesuaian</label>
                        <textarea name="notes" 
                                  x-model="editData.notes" 
                                  rows="3" 
                                  placeholder="Contoh: Disesuaikan karena kendala jaringan di lapangan / memo atasan..."
                                  class="w-full text-xs rounded-xl border-slate-300 p-3 bg-white text-slate-800 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-2xs"></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL PREVIEW FOTO SELFIE ABSENSI                              -->
        <!-- ============================================================== -->
        <div x-show="photoModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4"
             @click="photoModalOpen = false">
            <div class="bg-white rounded-2xl overflow-hidden max-w-md w-full shadow-2xl border border-slate-200" @click.stop>
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                    <span class="font-bold text-xs" x-text="'Foto Absen: ' + activePhotoName"></span>
                    <button type="button" @click="photoModalOpen = false" class="text-white hover:text-slate-300 font-bold text-lg leading-none cursor-pointer">&times;</button>
                </div>
                <div class="p-3 bg-slate-100 flex items-center justify-center min-h-[300px]">
                    <img :src="activePhoto" alt="Foto Absen" class="max-h-[450px] w-auto object-contain rounded-xl shadow-md">
                </div>
                <div class="p-3 bg-white border-t border-slate-200 flex justify-end">
                    <a :href="activePhoto" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Buka Ukuran Penuh ↗
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

