<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Lembur;
use App\Models\MonthlySchedule;
use App\Models\Pengajuan;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now('Asia/Jakarta');
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $shift = Shift::first() ?? Shift::create([
            'code' => 'P',
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_night' => false,
            'is_day_off' => false,
        ]);

        $atasanUsers = $users->filter(fn ($u) => $u->hasRole(['atasan', 'atasan_noc', 'atasan_teknik', 'atasan_umum', 'manager_teknisi', 'admin', 'super_admin']));
        $defaultAtasan = $atasanUsers->first() ?? $users->first();

        // -----------------------------------------------------------------
        // 1. SEED ATTENDANCE & SCHEDULES (Past 14 Days including Today)
        // -----------------------------------------------------------------
        for ($i = 13; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();
            $isWeekend = $date->isWeekend();

            foreach ($users->take(30) as $user) {
                // Ensure Monthly Schedule exists
                if (! MonthlySchedule::query()->where('user_id', $user->id)->whereDate('date', $dateStr)->exists()) {
                    MonthlySchedule::create([
                        'user_id' => $user->id,
                        'date' => $dateStr,
                        'shift_id' => $shift->id,
                        'is_locked' => false,
                    ]);
                }

                if ($isWeekend) {
                    continue;
                }

                // If today and not yet seeded, create realistic current status
                $rand = ($user->id * 7 + $i * 13) % 100;

                $saveAttendance = function (array $attributes) use ($user, $dateStr, $shift) {
                    $att = Attendance::query()
                        ->where('user_id', $user->id)
                        ->whereDate('date', $dateStr)
                        ->first();
                    $data = array_merge([
                        'user_id' => $user->id,
                        'date' => $dateStr,
                        'shift_id' => $shift->id,
                    ], $attributes);

                    if ($att) {
                        $att->update($data);
                    } else {
                        Attendance::create($data);
                    }
                };

                if ($i === 0) {
                    // Today: some already present on-time, some late, some leave
                    if ($rand < 60) {
                        $clockIn = $date->copy()->setTime(7, 45 + ($rand % 14), rand(10, 50));
                        $saveAttendance([
                            'clock_in_at' => $clockIn,
                            'status' => 'present',
                            'late_minutes' => 0,
                            'lat_in' => -6.9175,
                            'lng_in' => 107.6191,
                        ]);
                    } elseif ($rand < 85) {
                        $lateM = 10 + ($rand % 35);
                        $clockIn = $date->copy()->setTime(8, $lateM, rand(10, 50));
                        $saveAttendance([
                            'clock_in_at' => $clockIn,
                            'status' => 'late',
                            'late_minutes' => $lateM,
                            'lat_in' => -6.9175,
                            'lng_in' => 107.6191,
                        ]);
                    } elseif ($rand < 95) {
                        $saveAttendance([
                            'status' => 'leave',
                            'late_minutes' => 0,
                            'notes' => 'Izin keperluan keluarga / Cuti tahunan',
                        ]);
                    }
                    // remaining 5% haven't clocked in yet today
                } else {
                    // Past days: completed records
                    if ($rand < 70) {
                        // Present On-Time
                        $clockIn = $date->copy()->setTime(7, 45 + ($rand % 14), rand(10, 50));
                        $clockOut = $date->copy()->setTime(17, rand(0, 30), rand(10, 50));
                        $saveAttendance([
                            'clock_in_at' => $clockIn,
                            'clock_out_at' => $clockOut,
                            'status' => 'present',
                            'late_minutes' => 0,
                            'lat_in' => -6.9175,
                            'lng_in' => 107.6191,
                            'lat_out' => -6.9175,
                            'lng_out' => 107.6191,
                        ]);
                    } elseif ($rand < 88) {
                        // Present Late
                        $lateM = 12 + ($rand % 40);
                        $clockIn = $date->copy()->setTime(8, $lateM, rand(10, 50));
                        $clockOut = $date->copy()->setTime(17, 15, rand(10, 50));
                        $saveAttendance([
                            'clock_in_at' => $clockIn,
                            'clock_out_at' => $clockOut,
                            'status' => 'late',
                            'late_minutes' => $lateM,
                            'lat_in' => -6.9175,
                            'lng_in' => 107.6191,
                            'lat_out' => -6.9175,
                            'lng_out' => 107.6191,
                        ]);
                    } elseif ($rand < 95) {
                        // Cuti / Sakit
                        $saveAttendance([
                            'status' => 'leave',
                            'late_minutes' => 0,
                            'notes' => 'Cuti tahunan sah',
                        ]);
                    } else {
                        // Alpa
                        $saveAttendance([
                            'status' => 'alpa',
                            'late_minutes' => 0,
                            'notes' => 'Tidak hadir tanpa keterangan',
                        ]);
                    }
                }
            }
        }

        // -----------------------------------------------------------------
        // 2. SEED PENGAJUAN (Dana & Barang)
        // -----------------------------------------------------------------
        $samplePengajuan = [
            [
                'judul' => 'Pengadaan Patch Cord SC-UPC & Protection Sleeve',
                'keperluan' => 'Stok material perbaikan distribusi ODP Soreang',
                'jenis' => 'barang',
                'nominal' => 850000,
                'status' => 'terealisasi',
                'nominal_realisasi' => 840000,
                'days_ago' => 8,
            ],
            [
                'judul' => 'BBM & Operasional Mobil Sigra Tim Patroli FO',
                'keperluan' => 'Operasional patroli kabel FO jalur Soreang - Ciwidey',
                'jenis' => 'dana',
                'nominal' => 350000,
                'status' => 'ditransfer',
                'days_ago' => 5,
            ],
            [
                'judul' => 'Pembelian Modul SFP 10G Bidi 20KM 4 Pcs',
                'keperluan' => 'Upgrade link distribusi uplink OLT Babakan Tarogong',
                'jenis' => 'barang',
                'nominal' => 2400000,
                'status' => 'disetujui',
                'days_ago' => 3,
            ],
            [
                'judul' => 'Perbaikan & Servis AC Presisi Ruang Server NOC',
                'keperluan' => 'Pemeliharaan pendingin ruang core network',
                'jenis' => 'dana',
                'nominal' => 1250000,
                'status' => 'menunggu_finance',
                'days_ago' => 2,
            ],
            [
                'judul' => 'Penggantian Ban Mobil Operasional Luxio Tim Backbone',
                'keperluan' => 'Kondisi ban gundul berbahaya untuk tugas darurat malam hari',
                'jenis' => 'dana',
                'nominal' => 1800000,
                'status' => 'menunggu_atasan',
                'days_ago' => 1,
            ],
            [
                'judul' => 'Konsumsi & Logistik Lembur Maintenance FO Cut Gatsu',
                'keperluan' => 'Makan malam dan logistik tim lapangan 6 orang',
                'jenis' => 'dana',
                'nominal' => 210000,
                'status' => 'terealisasi',
                'nominal_realisasi' => 210000,
                'days_ago' => 6,
            ],
            [
                'judul' => 'Kabel Dropcore 1 Core 3 Seling 1000M 2 Roll',
                'keperluan' => 'Penarikan sambungan baru user cluster Tarogong',
                'jenis' => 'barang',
                'nominal' => 1600000,
                'status' => 'menunggu_atasan',
                'days_ago' => 0,
            ],
            [
                'judul' => 'Pembelian Fusion Splicer Spare Electrode',
                'keperluan' => 'Elektroda mesin splicer sudah mencapai 3000 arc discharge',
                'jenis' => 'barang',
                'nominal' => 450000,
                'status' => 'menunggu_direksi',
                'days_ago' => 4,
            ],
            [
                'judul' => 'Permintaan Aksesori Gaming Ruang Istirahat',
                'keperluan' => 'Tidak sesuai peruntukan operasional',
                'jenis' => 'barang',
                'nominal' => 650000,
                'status' => 'ditolak',
                'days_ago' => 7,
            ],
            [
                'judul' => 'Langganan Lisensi Monitoring Network Dude & Grafana Cloud',
                'keperluan' => 'Monitoring uptime SLA B2B',
                'jenis' => 'dana',
                'nominal' => 750000,
                'status' => 'disetujui',
                'days_ago' => 2,
            ],
        ];

        foreach ($samplePengajuan as $idx => $item) {
            $applicant = $users->get($idx % $users->count());
            $atasan = $applicant->supervisor_id ? $users->find($applicant->supervisor_id) : $defaultAtasan;
            $createdAt = $now->copy()->subDays($item['days_ago'])->subHours(rand(1, 8));

            Pengajuan::updateOrCreate(
                ['nomor_pengajuan' => sprintf('PJ-%s-%04d', $now->format('Ym'), 100 + $idx)],
                [
                    'jenis' => $item['jenis'],
                    'user_id' => $applicant->id,
                    'atasan_id' => $atasan ? $atasan->id : $defaultAtasan->id,
                    'judul' => $item['judul'],
                    'keperluan' => $item['keperluan'],
                    'nominal_total' => $item['nominal'],
                    'status' => $item['status'],
                    'nominal_realisasi' => $item['nominal_realisasi'] ?? null,
                    'selisih_realisasi' => isset($item['nominal_realisasi']) ? ($item['nominal'] - $item['nominal_realisasi']) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }

        // -----------------------------------------------------------------
        // 3. SEED LEMBUR (Overtime)
        // -----------------------------------------------------------------
        $sampleLembur = [
            [
                'alasan' => 'Penanganan darurat kabel FO putus tertabrak truk di Gatsu',
                'jenis' => 'darurat',
                'durasi' => 210, // 3j 30m
                'status' => 'paid',
                'days_ago' => 6,
            ],
            [
                'alasan' => 'Progres migrasi konsorsium fiber optik pusat',
                'jenis' => 'hari_kerja',
                'durasi' => 480, // 8j
                'status' => 'paid',
                'days_ago' => 5,
            ],
            [
                'alasan' => 'Penarikan 3 user baru di Babakan Tarogong',
                'jenis' => 'hari_kerja',
                'durasi' => 360, // 6j
                'status' => 'approved',
                'days_ago' => 3,
            ],
            [
                'alasan' => 'Perbaikan redaman tinggi OLT Sumarecon',
                'jenis' => 'hari_kerja',
                'durasi' => 180, // 3j
                'status' => 'approved',
                'days_ago' => 2,
            ],
            [
                'alasan' => 'Survey jalur distribusi FO untuk perluasan jaringan Soreang',
                'jenis' => 'hari_libur',
                'durasi' => 240, // 4j
                'status' => 'pending',
                'days_ago' => 1,
            ],
            [
                'alasan' => 'Maintenance switch core data center malam hari',
                'jenis' => 'hari_kerja',
                'durasi' => 150, // 2j 30m
                'status' => 'pending',
                'days_ago' => 0,
            ],
            [
                'alasan' => 'Lembur monitoring Pilkada serentak di command center',
                'jenis' => 'hari_libur',
                'durasi' => 420, // 7j
                'status' => 'approved',
                'days_ago' => 4,
            ],
            [
                'alasan' => 'Re-splicing joint closure ODC-02 Soreang',
                'jenis' => 'darurat',
                'durasi' => 180, // 3j
                'status' => 'paid',
                'days_ago' => 7,
            ],
            [
                'alasan' => 'Lembur tanpa SPK dan tanpa persetujuan awal',
                'jenis' => 'hari_kerja',
                'durasi' => 120,
                'status' => 'rejected',
                'days_ago' => 8,
            ],
        ];

        foreach ($sampleLembur as $idx => $item) {
            $employee = $users->get(($idx + 2) % $users->count());
            $atasan = $employee->supervisor_id ? $users->find($employee->supervisor_id) : $defaultAtasan;
            $tgl = $now->copy()->subDays($item['days_ago']);

            $start = $tgl->copy()->setTime(17, 30);
            $end = $start->copy()->addMinutes($item['durasi']);

            Lembur::updateOrCreate(
                ['nomor_lembur' => sprintf('LBR-%s-%04d', $now->format('Ym'), 500 + $idx)],
                [
                    'jenis_lembur' => $item['jenis'],
                    'user_id' => $employee->id,
                    'atasan_id' => $atasan ? $atasan->id : $defaultAtasan->id,
                    'tanggal' => $tgl->toDateString(),
                    'jam_mulai' => $start,
                    'jam_selesai' => $end,
                    'durasi_menit' => $item['durasi'],
                    'alasan' => $item['alasan'],
                    'status' => $item['status'],
                    'approver_id' => in_array($item['status'], ['approved', 'paid']) ? ($atasan ? $atasan->id : $defaultAtasan->id) : null,
                    'approved_at' => in_array($item['status'], ['approved', 'paid']) ? $tgl->copy()->addDay() : null,
                    'paid_at' => $item['status'] === 'paid' ? $tgl->copy()->addDays(2) : null,
                    'paid_by' => $item['status'] === 'paid' ? $defaultAtasan->id : null,
                ]
            );
        }
    }
}
