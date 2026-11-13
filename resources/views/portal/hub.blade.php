<x-layouts.app title="Dashboard Operasional &amp; Statistik">
    <!-- Hero Welcome Banner (self-contained CSS so it never depends on compiled Tailwind) -->
    @php
        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $firstName = $nameParts[0] ?? $user->name;
        $restName = $nameParts[1] ?? '';
        $clockInTime = $metrics['attendance_today']['clock_in_time'] ?? null;
        $scopeType = $stats['scope']['type'] ?? 'self';
    @endphp
    <style>
        .hh{position:relative;overflow:hidden;border-radius:26px;margin-bottom:24px;border:1px solid rgba(45,212,191,.18);box-shadow:0 16px 40px -8px rgba(2,24,27,.45),inset 0 1px 0 rgba(94,234,212,.2);background:radial-gradient(90% 120% at 15% 15%,#0c3b3e 0%,#07272a 50%,#04191c 100%);color:#fff;user-select:none}
        .hh-glow1{position:absolute;left:-90px;top:-90px;width:300px;height:300px;border-radius:50%;background:rgba(20,184,166,.14);filter:blur(65px);pointer-events:none}
        .hh-glow2{position:absolute;right:40px;bottom:-20px;width:340px;height:340px;border-radius:50%;background:rgba(45,212,191,.12);filter:blur(65px);pointer-events:none}
        .hh-inner{position:relative;z-index:2;display:flex;align-items:flex-end;justify-content:space-between;gap:32px;padding:0 48px;min-height:365px}
        .hh-left{min-width:0;flex:1;padding:34px 0;align-self:center}
        .hh-date{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;background:rgba(13,148,136,.18);border:1px solid rgba(45,212,191,.24);font-size:12px;font-weight:600;color:#99f6e4;box-shadow:inset 0 1px 0 rgba(255,255,255,.06)}
        .hh-hello{margin:18px 0 6px;font-size:12px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;color:#5eead4}
        .hh-name{margin:0;font-weight:800;text-transform:uppercase;line-height:.96;letter-spacing:-.025em;font-size:clamp(36px,4.6vw,58px)}
        .hh-name .a{display:block;color:#fff}
        .hh-name .b{display:block;color:#14b8a6;background:linear-gradient(90deg,#2dd4bf 0%,#14b8a6 55%,#0d9488 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;padding-bottom:2px}
        .hh-sub{margin:12px 0 0;font-size:13.5px;line-height:1.55;color:rgba(204,251,241,.74);max-width:460px}
        .hh-pills{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:16px}
        .hh-pill{display:inline-flex;align-items:center;gap:6px;padding:5.5px 13px;border-radius:999px;font-size:11.5px;font-weight:600;background:rgba(4,22,25,.55);color:#e2e8f0;border:1px solid rgba(255,255,255,.12)}
        .hh-pill.ok{background:rgba(16,185,129,.14);color:#6ee7b7;border-color:rgba(52,211,153,.28);font-weight:700}
        .hh-dot{width:6px;height:6px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399}
        .hh-cta{display:inline-flex;align-items:center;gap:8px;margin-top:20px;padding:11px 22px;border-radius:999px;font-size:13px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,#14b8a6 0%,#0d9488 100%);box-shadow:0 8px 22px -4px rgba(13,148,136,.55),inset 0 1px 0 rgba(255,255,255,.22);transition:transform .15s ease,filter .15s ease}
        .hh-cta:hover{filter:brightness(1.08);transform:translateY(-1px)}
        .hh-right{position:relative;flex:none;width:380px;height:365px;margin-right:16px;align-self:flex-end}
        .hh-ring{position:absolute;left:22px;right:22px;top:42px;bottom:0;border-radius:999px 999px 0 0;border:1px solid rgba(94,234,212,.16);pointer-events:none;z-index:1}
        .hh-dot-p{position:absolute;top:64px;left:8px;width:8px;height:8px;border-radius:50%;background:#a78bfa;box-shadow:0 0 12px rgba(167,139,250,.85);pointer-events:none;z-index:1}
        .hh-dot-c{position:absolute;bottom:126px;left:0;width:6px;height:6px;border-radius:50%;background:#5eead4;box-shadow:0 0 10px rgba(94,234,212,.85);pointer-events:none;z-index:1}
        .hh-arch{position:absolute;left:48px;right:48px;top:68px;bottom:0;border-radius:999px 999px 0 0;background:linear-gradient(180deg,#2dd4bf 0%,#14b8a6 45%,#0d9488 100%);box-shadow:0 -10px 35px rgba(20,184,166,.22);z-index:1}
        .hh-photo{position:absolute;left:0;right:0;bottom:-28px;height:365px;z-index:2;display:flex;align-items:flex-end;justify-content:center;pointer-events:none}
        .hh-photo img{width:100%;height:100%;object-fit:contain;object-position:center bottom;display:block;transform:scale(1.11);transform-origin:50% 100%;filter:drop-shadow(0 12px 24px rgba(0,0,0,.42))}
        .hh-initials{position:absolute;left:48px;right:48px;top:68px;bottom:0;border-radius:999px 999px 0 0;display:none;align-items:center;justify-content:center;font-size:68px;font-weight:800;color:rgba(255,255,255,.92);z-index:2}
        .hh-initials.show{display:flex}
        .hh-check{position:absolute;left:-24px;bottom:38px;z-index:3;display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:16px;background:rgba(6,34,38,.90);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(94,234,212,.22);box-shadow:0 12px 28px rgba(0,0,0,.45)}
        .hh-check .ic{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(16,185,129,.2)}
        .hh-check .ic i{width:10px;height:10px;border-radius:50%;background:#34d399;display:block;box-shadow:0 0 8px #34d399}
        .hh-check.no .ic{background:rgba(245,158,11,.2)}
        .hh-check.no .ic i{background:#fbbf24;box-shadow:0 0 8px #fbbf24}
        .hh-check b{display:block;font-size:12px;color:#fff;letter-spacing:.01em}
        .hh-check small{display:block;font-size:10.5px;color:rgba(153,246,228,.75);margin-top:1px}
        @media (min-width:768px) and (max-width:1100px){
            .hh-inner{padding:0 28px;min-height:330px;gap:20px}
            .hh-right{width:320px;height:325px;margin-right:0}
            .hh-ring{left:18px;right:18px;top:36px}
            .hh-arch,.hh-initials{left:40px;right:40px;top:60px}
            .hh-photo{bottom:-24px;height:325px}
        }
        @media (max-width:767px){
            .hh{border-radius:22px;margin-bottom:20px}
            .hh-inner{flex-direction:column;align-items:stretch;padding:22px 20px 0;gap:8px;min-height:auto}
            .hh-left{padding:0;align-self:stretch;text-align:left}
            .hh-date{font-size:11px;padding:5px 12px}
            .hh-hello{margin:14px 0 4px;font-size:11px;letter-spacing:.2em}
            .hh-name{font-size:clamp(30px,9.5vw,42px)}
            .hh-sub{margin:8px 0 0;font-size:12.5px}
            .hh-pills{margin-top:12px;gap:6px}
            .hh-pill{font-size:11px;padding:4.5px 11px}
            .hh-cta{margin-top:14px;padding:10px 18px;font-size:12px}
            .hh-right{width:280px;height:260px;margin:4px auto 0;align-self:center}
            .hh-ring{left:16px;right:16px;top:28px}
            .hh-arch,.hh-initials{left:36px;right:36px;top:48px}
            .hh-photo{left:0;right:0;bottom:-20px;height:260px}
            .hh-photo img{transform:scale(1.09)}
            .hh-check{left:-8px;bottom:16px;padding:8px 13px;border-radius:14px}
            .hh-initials{font-size:52px}
        }
    </style>
    <div class="hh">
        <div class="hh-glow1"></div>
        <div class="hh-glow2"></div>
        <div class="hh-inner">
            <div class="hh-left">
                <div class="hh-date">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span id="hub-live-clock">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y · H:i:s') }} WIB</span>
                </div>
                <p class="hh-hello">Selamat Datang 👋</p>
                <h1 class="hh-name">
                    <span class="a">{{ $firstName }}</span>
                    @if ($restName !== '')
                        <span class="b">{{ $restName }}</span>
                    @endif
                </h1>
                <p class="hh-sub">{{ $stats['scope']['subtitle'] }}</p>
                <div class="hh-pills">
                    <span class="hh-pill ok"><span class="hh-dot" style="{{ $user->is_active ? '' : 'background:#fb7185;box-shadow:0 0 8px #fb7185' }}"></span>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <span class="hh-pill">NIK: {{ $user->employee_code ?? 'MSN' }}</span>
                    @if ($user->division)
                        <span class="hh-pill">{{ $user->division }}</span>
                    @endif
                </div>
                <a href="{{ route('portal.profile') }}" class="hh-cta">
                    @if ($scopeType === 'company')
                        🏢 {{ $stats['scope']['badge'] ?? 'Seluruh Perusahaan' }}
                    @elseif ($scopeType === 'division')
                        👥 {{ $stats['scope']['badge'] }}
                    @else
                        📊 Statistik Pribadi
                    @endif
                </a>
            </div>

            <div class="hh-right">
                <div class="hh-ring"></div>
                <span class="hh-dot-p"></span>
                <span class="hh-dot-c"></span>
                <div class="hh-arch"></div>
                <div class="hh-photo">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                             onerror="this.style.display='none'; this.nextElementSibling.classList.add('show');">
                        <div class="hh-initials">{{ $user->initials }}</div>
                    @else
                        <div class="hh-initials show">{{ $user->initials }}</div>
                    @endif
                </div>
                <div class="hh-check {{ $clockInTime ? '' : 'no' }}">
                    <span class="ic"><i></i></span>
                    <div>
                        <b>{{ $clockInTime ? 'Hadir · ' . $clockInTime : 'Belum Check-in' }}</b>
                        <small>{{ $clockInTime ? 'Check-in hari ini' : 'Belum ada absen hari ini' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var el = document.getElementById('hub-live-clock');
            if (!el) return;
            var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            function tick() {
                var parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', hour12: false, year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'short' }).formatToParts(new Date());
                var m = {}; parts.forEach(function (p) { m[p.type] = p.value; });
                var dow = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].indexOf(m.weekday);
                el.textContent = days[dow] + ', ' + String(m.day).padStart(2, '0') + ' ' + months[parseInt(m.month, 10) - 1] + ' ' + m.year + ' · ' + m.hour + ':' + m.minute + ':' + m.second + ' WIB';
            }
            tick(); setInterval(tick, 1000);
        })();
    </script>

    <!-- Section 1: KPI Summary Cards (Absen, Pengajuan, Lembur) -->
    <section class="mb-8" aria-labelledby="heading-stats-kpi">
        <h2 id="heading-stats-kpi" class="sr-only">Ringkasan Statistik Utama</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Absensi & Disiplin -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-sm">
                                📅
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Absensi &amp; Disiplin</h3>
                                <span class="text-[11px] text-slate-400">Status Hari Ini &amp; Bulan Ini</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200/60">
                            {{ $stats['absensi']['month_rate'] }}% Hadir
                        </span>
                    </div>

                    <!-- Today Breakdown Pills Grid -->
                    <div class="grid grid-cols-4 gap-2 my-3.5">
                        <div class="rounded-xl bg-emerald-50/70 border border-emerald-100 p-2 text-center">
                            <span class="block text-lg font-bold text-emerald-700 leading-tight">{{ $stats['absensi']['today_present'] }}</span>
                            <span class="text-[10px] font-semibold text-emerald-800">Tepat</span>
                        </div>
                        <div class="rounded-xl bg-amber-50/70 border border-amber-100 p-2 text-center">
                            <span class="block text-lg font-bold text-amber-700 leading-tight">{{ $stats['absensi']['today_late'] }}</span>
                            <span class="text-[10px] font-semibold text-amber-800">Telat</span>
                        </div>
                        <div class="rounded-xl bg-blue-50/70 border border-blue-100 p-2 text-center">
                            <span class="block text-lg font-bold text-blue-700 leading-tight">{{ $stats['absensi']['today_leave'] }}</span>
                            <span class="text-[10px] font-semibold text-blue-800">Izin/Cuti</span>
                        </div>
                        <div class="rounded-xl bg-rose-50/70 border border-rose-100 p-2 text-center">
                            <span class="block text-lg font-bold text-rose-700 leading-tight">{{ $stats['absensi']['today_absent'] }}</span>
                            <span class="text-[10px] font-semibold text-rose-800">Alpa/Blm</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 space-y-1">
                        <div class="flex items-center justify-between">
                            <span>Akumulasi Hadir Bulan Ini:</span>
                            <strong class="text-slate-800 font-semibold">{{ $stats['absensi']['month_present_days'] }} Hari</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Total Keterlambatan:</span>
                            <strong class="text-slate-800 font-semibold">{{ $stats['absensi']['month_late_minutes'] }} Menit</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Pembaruan realtime</span>
                    <a href="{{ route('attendance.index') }}" class="font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 group">
                        Lihat Absen
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 2: Pengajuan (Dana & Barang) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-sm">
                                💳
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pengajuan Dana &amp; Barang</h3>
                                <span class="text-[11px] text-slate-400">Periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $stats['pengajuan']['pending'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-600' }}">
                            {{ $stats['pengajuan']['pending'] }} Menunggu
                        </span>
                    </div>

                    <div class="my-3">
                        <span class="text-xs text-slate-400 block font-medium">Total Nominal Diajukan</span>
                        <div class="text-xl sm:text-2xl font-bold font-heading text-slate-900 tracking-tight mt-0.5">
                            Rp {{ number_format($stats['pengajuan']['nominal_total'], 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Pengajuan Status Count Grid -->
                    <div class="grid grid-cols-3 gap-2 my-3">
                        <div class="rounded-xl bg-slate-50 p-2 text-center border border-slate-100">
                            <span class="block text-base font-bold text-slate-800 leading-tight">{{ $stats['pengajuan']['total'] }}</span>
                            <span class="text-[10px] text-slate-500 font-medium">Total Diajukan</span>
                        </div>
                        <div class="rounded-xl bg-emerald-50/70 p-2 text-center border border-emerald-100">
                            <span class="block text-base font-bold text-emerald-700 leading-tight">{{ $stats['pengajuan']['approved'] }}</span>
                            <span class="text-[10px] text-emerald-800 font-medium">Disetujui/Cair</span>
                        </div>
                        <div class="rounded-xl bg-rose-50/70 p-2 text-center border border-rose-100">
                            <span class="block text-base font-bold text-rose-700 leading-tight">{{ $stats['pengajuan']['rejected'] }}</span>
                            <span class="text-[10px] text-rose-800 font-medium">Ditolak</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 flex items-center justify-between">
                        <span>Nominal Disetujui/Cair:</span>
                        <strong class="text-emerald-700 font-semibold font-mono">Rp {{ number_format($stats['pengajuan']['nominal_disetujui'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Realisasi: Rp {{ number_format($stats['pengajuan']['nominal_realisasi'], 0, ',', '.') }}</span>
                    <a href="{{ route('gomsn.pengajuan.index') }}" class="font-semibold text-cyan-700 hover:text-cyan-800 flex items-center gap-1 group">
                        Buka Pengajuan
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 3: Lembur (Overtime) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                ⏱️
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lembur (Overtime)</h3>
                                <span class="text-[11px] text-slate-400">Total Akumulasi Kerja Tambahan</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $stats['lembur']['pending'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-600' }}">
                            {{ $stats['lembur']['pending'] }} Menunggu
                        </span>
                    </div>

                    <div class="my-3">
                        <span class="text-xs text-slate-400 block font-medium">Total Durasi Lembur Disetujui</span>
                        <div class="text-xl sm:text-2xl font-bold font-heading text-slate-900 tracking-tight mt-0.5 flex items-baseline gap-1.5">
                            <span>{{ $stats['lembur']['total_hours'] }}</span>
                            <span class="text-sm font-normal text-slate-500">Jam Kerja</span>
                        </div>
                    </div>

                    <!-- Lembur Status Count Grid -->
                    <div class="grid grid-cols-3 gap-2 my-3">
                        <div class="rounded-xl bg-slate-50 p-2 text-center border border-slate-100">
                            <span class="block text-base font-bold text-slate-800 leading-tight">{{ $stats['lembur']['total'] }}</span>
                            <span class="text-[10px] text-slate-500 font-medium">Pengajuan</span>
                        </div>
                        <div class="rounded-xl bg-emerald-50/70 p-2 text-center border border-emerald-100">
                            <span class="block text-base font-bold text-emerald-700 leading-tight">{{ $stats['lembur']['approved'] }}</span>
                            <span class="text-[10px] text-emerald-800 font-medium">Disetujui</span>
                        </div>
                        <div class="rounded-xl bg-blue-50/70 p-2 text-center border border-blue-100">
                            <span class="block text-base font-bold text-blue-700 leading-tight">{{ $stats['lembur']['paid'] }}</span>
                            <span class="text-[10px] text-blue-800 font-medium">Dibayar</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 flex items-center justify-between">
                        <span>Lembur Ditolak:</span>
                        <strong class="text-slate-700 font-semibold">{{ $stats['lembur']['rejected'] }} Pengajuan</strong>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Kompensasi &amp; SPK</span>
                    <a href="{{ route('gomsn.overtime.index') }}" class="font-semibold text-indigo-700 hover:text-indigo-800 flex items-center gap-1 group">
                        Kelola Lembur
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Visualisasi Grafik Informatif (Interactive Charts) -->
    <section class="mb-8" aria-labelledby="heading-charts">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 id="heading-charts" class="font-heading text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-teal-100 text-teal-800 text-xs">📊</span>
                    Grafik &amp; Analitik Operasional
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Data tren kehadiran, komposisi pengajuan, dan jam lembur 7 hari terakhir
                </p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Data
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
            <!-- Chart 1: Tren Kehadiran 7 Hari Terakhir (Col 8) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                        <div>
                            <h3 class="font-heading font-bold text-sm text-slate-800">
                                Tren Kehadiran Harian (7 Hari Terakhir)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Distribusi status tepat waktu, keterlambatan, izin, dan alpa
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] font-medium text-slate-600 flex-wrap">
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-teal-600 inline-block"></span> Tepat</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500 inline-block"></span> Telat</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-indigo-500 inline-block"></span> Izin</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span> Alpa</span>
                        </div>
                    </div>

                    <div class="relative w-full h-[250px] sm:h-[280px]">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>*Data dihitung berdasarkan jadwal kerja harian (Shift &amp; Presensi)</span>
                    <a href="{{ route('attendance.leaderboard') }}" class="font-semibold text-teal-700 hover:underline">
                        Lihat Leaderboard &rarr;
                    </a>
                </div>
            </div>

            <!-- Chart 2: Komposisi Pengajuan (Col 4) -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="pb-3 mb-3 border-b border-slate-100">
                        <h3 class="font-heading font-bold text-sm text-slate-800">
                            Komposisi Status Pengajuan
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Rasio persetujuan kasbon &amp; barang bulan ini
                        </p>
                    </div>

                    <div class="relative w-full h-[200px] flex items-center justify-center my-2">
                        <canvas id="pengajuanDonutChart"></canvas>
                    </div>

                    <!-- Status Breakdown List -->
                    <div class="space-y-2 mt-4 text-xs">
                        <div class="flex items-center justify-between p-2 rounded-xl bg-teal-50/60 border border-teal-100">
                            <span class="flex items-center gap-2 font-medium text-teal-900">
                                <span class="h-2 w-2 rounded-full bg-teal-600"></span> Disetujui / Cair
                            </span>
                            <strong class="font-bold text-teal-800">{{ $stats['pengajuan']['approved'] }} Item</strong>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-amber-50/60 border border-amber-100">
                            <span class="flex items-center gap-2 font-medium text-amber-900">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Menunggu Approval
                            </span>
                            <strong class="font-bold text-amber-800">{{ $stats['pengajuan']['pending'] }} Item</strong>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-rose-50/60 border border-rose-100">
                            <span class="flex items-center gap-2 font-medium text-rose-900">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span> Ditolak
                            </span>
                            <strong class="font-bold text-rose-800">{{ $stats['pengajuan']['rejected'] }} Item</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 text-center text-xs text-slate-400">
                    Total {{ $stats['pengajuan']['total'] }} Pengajuan di periode ini
                </div>
            </div>
        </div>

        <!-- Chart 3: Tren Jam Lembur Mingguan (Full Width Col 12) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="font-heading font-bold text-sm text-slate-800">
                        Tren Jam Lembur Harian (7 Hari Terakhir)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Akumulasi total jam lembur yang telah disetujui &amp; diverifikasi per hari
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Total 7 Hari: {{ array_sum($stats['charts']['lembur']['hours']) }} Jam
                    </span>
                </div>
            </div>

            <div class="relative w-full h-[220px] sm:h-[240px]">
                <canvas id="overtimeTrendChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Section 3: Aktivitas Terkini (Pengajuan & Lembur Realtime) -->
    <section class="mb-8" aria-labelledby="heading-recent-activities">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Table 1: Pengajuan Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-sm">
                                💳
                            </span>
                            <div>
                                <h3 class="font-heading font-bold text-sm text-slate-800">Pengajuan Terbaru</h3>
                                <span class="text-[11px] text-slate-400">Dana &amp; Pengadaan Barang</span>
                            </div>
                        </div>
                        <a href="{{ route('gomsn.pengajuan.index') }}" class="text-xs font-semibold text-cyan-700 hover:underline">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-2.5">Item &amp; Pemohon</th>
                                    <th class="pb-2.5 px-2">Nominal</th>
                                    <th class="pb-2.5 px-2">Status</th>
                                    <th class="pb-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($stats['recent']['pengajuan'] as $p)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5">
                                            <div class="font-semibold text-slate-800 line-clamp-1">{{ $p->judul }}</div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-cyan-700">{{ $p->nomor_pengajuan }}</span>
                                                <span>·</span>
                                                <span>{{ $p->user->name ?? 'User' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2 font-mono font-semibold text-slate-700">
                                            Rp {{ number_format($p->nominal_total, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-2">
                                            @if (in_array($p->status, ['disetujui', 'ditransfer', 'terealisasi']))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ strtoupper(str_replace('_', ' ', $p->status)) }}
                                                </span>
                                            @elseif (str_starts_with($p->status, 'menunggu'))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    MENUNGGU
                                                </span>
                                            @elseif ($p->status === 'ditolak')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    DITOLAK
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ strtoupper($p->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            <a href="{{ route('gomsn.pengajuan.show', $p->id) }}" class="font-semibold text-cyan-700 hover:text-cyan-800">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                            Belum ada pengajuan terkini di filter ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table 2: Lembur Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                ⏱️
                            </span>
                            <div>
                                <h3 class="font-heading font-bold text-sm text-slate-800">Lembur Terbaru</h3>
                                <span class="text-[11px] text-slate-400">Pekerjaan Tambahan &amp; Darurat</span>
                            </div>
                        </div>
                        <a href="{{ route('gomsn.overtime.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-2.5">Karyawan &amp; Alasan</th>
                                    <th class="pb-2.5 px-2">Tanggal &amp; Durasi</th>
                                    <th class="pb-2.5 px-2">Status</th>
                                    <th class="pb-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($stats['recent']['lembur'] as $l)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5">
                                            <div class="font-semibold text-slate-800">{{ $l->user->name ?? 'User' }}</div>
                                            <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                                {{ $l->alasan ?: 'Lembur operasional' }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            <div class="font-semibold text-slate-700">
                                                {{ round($l->durasi_menit / 60, 1) }} Jam
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ \Carbon\Carbon::parse($l->tanggal)->translatedFormat('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            @if ($l->status === 'approved')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    DISETUJUI
                                                </span>
                                            @elseif ($l->status === 'paid')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    DIBAYAR
                                                </span>
                                            @elseif ($l->status === 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    MENUNGGU
                                                </span>
                                            @elseif ($l->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    DITOLAK
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ strtoupper($l->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            <a href="{{ route('gomsn.overtime.index') }}" class="font-semibold text-indigo-700 hover:text-indigo-800">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
                                            Belum ada data lembur terkini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 4: Quick Access Modul Utama -->
    <section class="mb-8" aria-labelledby="heading-quick-access">
        <div class="flex items-center justify-between mb-3.5">
            <h2 id="heading-quick-access" class="font-heading text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="p-1 rounded-lg bg-teal-100 text-teal-800 text-xs">⚡</span>
                Pintasan Cepat Modul
            </h2>
            <span class="text-xs text-slate-400 font-medium">Akses langsung fitur operasional harian</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <!-- Card 1: Presensi Biometrik -->
            <a href="{{ route('attendance.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-teal-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                            PRESENSI HRIS
                        </span>
                        <span class="text-emerald-600 font-bold text-xs">✓ Online</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-teal-700 transition-colors">
                        Presensi &amp; Face Biometrik
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Clock-in, clock-out dengan validasi GPS &amp; AI deteksi wajah.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-teal-700 font-semibold">
                    <span>Buka Absen</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 2: Pengajuan Kasbon & Barang -->
            <a href="{{ route('gomsn.pengajuan.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-cyan-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-md border border-cyan-100">
                            FINANCE &amp; LOGISTIK
                        </span>
                        <span class="text-amber-600 font-bold text-xs">{{ $stats['pengajuan']['pending'] }} Pending</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-cyan-700 transition-colors">
                        Pengajuan Dana &amp; Barang
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Buat permohonan kasbon, reimburs, atau pengadaan alat kerja.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-cyan-700 font-semibold">
                    <span>Ajukan Sekarang</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 3: Lembur (Overtime) -->
            <a href="{{ route('gomsn.overtime.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-indigo-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                            OPERASIONAL &amp; SPK
                        </span>
                        <span class="text-indigo-600 font-bold text-xs">{{ $stats['lembur']['total_hours'] }} Jam</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-indigo-700 transition-colors">
                        Pengajuan Lembur
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Klaim jam lembur darurat lapangan, hari kerja atau hari libur.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-700 font-semibold">
                    <span>Kelola Lembur</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 4: Jaringan OLT, PON & ODP -->
            <a href="{{ route('gomsn.network.index') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-teal-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                            INFRASTRUKTUR NOC
                        </span>
                        <span class="text-emerald-600 font-bold text-xs">Aktif</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-teal-700 transition-colors">
                        OLT, PON &amp; ODP
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Peta topologi fiber, distribusi port splitter ODP &amp; nomor internet pelanggan.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-teal-700 font-semibold">
                    <span>Buka Jaringan</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 5: Leaderboard Disiplin -->
            <a href="{{ route('attendance.leaderboard') }}" class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:border-emerald-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                            RANKING DISIPLIN
                        </span>
                        <span class="text-amber-500 font-bold text-xs">🏆 Top 10</span>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900 mt-2 group-hover:text-emerald-700 transition-colors">
                        Leaderboard Kehadiran
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Pantau peringkat kehadiran terbaik per divisi dan perusahaan.
                    </p>
                </div>
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-700 font-semibold">
                    <span>Lihat Ranking</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Chart.js Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not yet loaded');
                return;
            }

            const chartFontFamily = 'Plus Jakarta Sans, Inter, sans-serif';

            // 1. Attendance Trend Chart (Stacked / Grouped Bar)
            const attCanvas = document.getElementById('attendanceTrendChart');
            if (attCanvas) {
                const attData = @json($stats['charts']['attendance']);
                new Chart(attCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: attData.labels,
                        datasets: [
                            {
                                label: 'Tepat Waktu',
                                data: attData.present,
                                backgroundColor: '#0D9488',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Terlambat',
                                data: attData.late,
                                backgroundColor: '#F59E0B',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Izin / Cuti',
                                data: attData.leave,
                                backgroundColor: '#6366F1',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            },
                            {
                                label: 'Alpa / Belum',
                                data: attData.absent,
                                backgroundColor: '#EF4444',
                                borderRadius: 6,
                                maxBarThickness: 28,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                titleFont: { family: chartFontFamily, size: 12, weight: 'bold' },
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { font: { family: chartFontFamily, size: 11 }, color: '#64748B' }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: '#F1F5F9' },
                                ticks: {
                                    stepSize: 1,
                                    font: { family: chartFontFamily, size: 11 },
                                    color: '#64748B'
                                }
                            }
                        }
                    }
                });
            }

            // 2. Pengajuan Donut Chart
            const pengajuanCanvas = document.getElementById('pengajuanDonutChart');
            if (pengajuanCanvas) {
                const pengData = @json($stats['charts']['pengajuan']);
                const totalItems = pengData.data.reduce((a, b) => a + b, 0);

                new Chart(pengajuanCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: pengData.labels,
                        datasets: [{
                            data: totalItems > 0 ? pengData.data : [1],
                            backgroundColor: totalItems > 0 ? pengData.colors : ['#E2E8F0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: totalItems > 0,
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        const val = context.raw || 0;
                                        const pct = totalItems > 0 ? Math.round((val / totalItems) * 100) : 0;
                                        return ` ${context.label}: ${val} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Overtime Trend Chart
            const lemburCanvas = document.getElementById('overtimeTrendChart');
            if (lemburCanvas) {
                const lemburData = @json($stats['charts']['lembur']);
                const ctx = lemburCanvas.getContext('2d');
                
                // Gradient fill
                const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: lemburData.labels,
                        datasets: [
                            {
                                label: 'Jam Lembur Disetujui',
                                data: lemburData.hours,
                                borderColor: '#6366F1',
                                borderWidth: 2.5,
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#6366F1',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8,
                                bodyFont: { family: chartFontFamily, size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return ` Durasi: ${context.raw} Jam`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: chartFontFamily, size: 11 }, color: '#64748B' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#F1F5F9' },
                                ticks: {
                                    font: { family: chartFontFamily, size: 11 },
                                    color: '#64748B',
                                    callback: function(value) {
                                        return value + 'j';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
