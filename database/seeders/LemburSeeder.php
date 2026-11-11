<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Lembur;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class LemburSeeder extends Seeder
{
    /**
     * Run database seeds to populate realistic overtime approval data.
     */
    public function run(): void
    {
        // 0. Ensure Roles Exist
        Role::firstOrCreate(['name' => 'atasan']);
        Role::firstOrCreate(['name' => 'finance']);
        Role::firstOrCreate(['name' => 'hrd']);
        Role::firstOrCreate(['name' => 'karyawan']);

        // 1. Resolve Supervisors & Key Users
        $atasanTeknik = User::where('username', 'Ipin Aripin')->first()
            ?? User::where('username', 'mgr_teknis')->first()
            ?? User::role('atasan')->where('division', 'Teknik')->first();

        if (! $atasanTeknik) {
            $atasanTeknik = User::firstOrCreate(
                ['username' => 'Ipin Aripin'],
                [
                    'name' => 'Ipin Aripin',
                    'email' => 'ipin@gomsn.net',
                    'password' => Hash::make('password'),
                    'employee_code' => 'MGR-TK-001',
                    'division' => 'Teknik',
                    'is_active' => true,
                ]
            );
            $atasanTeknik->assignRole('atasan');
        }

        $financeUser = User::where('username', 'finance')->first()
            ?? User::role('finance')->first();

        if (! $financeUser) {
            $financeUser = User::firstOrCreate(
                ['username' => 'finance'],
                [
                    'name' => 'Finance',
                    'email' => 'finance@gomsn.net',
                    'password' => Hash::make('password'),
                    'employee_code' => 'KR6465',
                    'division' => 'Finance',
                    'position' => 'Finance & Akuntansi',
                    'is_active' => true,
                ]
            );
            $financeUser->assignRole('finance');
        }

        $hrdUser = User::where('username', 'hrd')->first()
            ?? User::role('hrd')->first();

        if (! $hrdUser) {
            $hrdUser = User::firstOrCreate(
                ['username' => 'hrd'],
                [
                    'name' => 'HRD Admin',
                    'email' => 'hrd@gomsn.net',
                    'password' => Hash::make('password'),
                    'employee_code' => 'KR-HR-001',
                    'division' => 'HRD',
                    'is_active' => true,
                ]
            );
            $hrdUser->assignRole('hrd');
        }

        $defaultOffice = OfficeLocation::where('code', 'BDG-HQ')->first()
            ?? OfficeLocation::first();

        $shiftPagi = Shift::where('code', 'P')->first() ?? Shift::first();

        // 2. Resolve or Create the exact 5 Employees from Mockup
        // Budi
        $budi = User::firstOrCreate(
            ['username' => 'Budi'],
            [
                'name' => 'Budi',
                'email' => 'budi@gomsn.net',
                'phone' => '08120000001',
                'password' => Hash::make('password'),
                'employee_code' => 'KR-TK-001',
                'division' => 'Teknik',
                'is_active' => true,
            ]
        );
        $budi->division = 'Teknik';
        $budi->save();
        if (! $budi->hasRole('karyawan')) {
            $budi->assignRole('karyawan');
        }

        // Dede Rahman
        $dede = User::firstOrCreate(
            ['username' => 'Dede Rahman'],
            [
                'name' => 'Dede Rahman',
                'email' => 'dede.rahman@gomsn.net',
                'phone' => '08120000002',
                'password' => Hash::make('password'),
                'employee_code' => 'KR-TK-002',
                'division' => 'Teknik',
                'is_active' => true,
            ]
        );
        $dede->division = 'Teknik';
        $dede->save();
        if (! $dede->hasRole('karyawan')) {
            $dede->assignRole('karyawan');
        }

        // Ohang
        $ohang = User::firstOrCreate(
            ['username' => 'Ohang'],
            [
                'name' => 'Ohang',
                'email' => 'ohang@gomsn.net',
                'phone' => '08120000003',
                'password' => Hash::make('password'),
                'employee_code' => 'KR-TK-003',
                'division' => 'Teknik',
                'is_active' => true,
            ]
        );
        $ohang->division = 'Teknik';
        $ohang->save();
        if (! $ohang->hasRole('karyawan')) {
            $ohang->assignRole('karyawan');
        }

        // Dudi Durahman
        $dudi = User::firstOrCreate(
            ['username' => 'Dudi Durahman'],
            [
                'name' => 'Dudi Durahman',
                'email' => 'dudi.durahman@gomsn.net',
                'phone' => '08120000004',
                'password' => Hash::make('password'),
                'employee_code' => 'KR-TK-004',
                'division' => 'Teknik',
                'is_active' => true,
            ]
        );
        $dudi->division = 'Teknik';
        $dudi->save();
        if (! $dudi->hasRole('karyawan')) {
            $dudi->assignRole('karyawan');
        }

        // Ryan Gondel
        $ryan = User::firstOrCreate(
            ['username' => 'Ryan Gondel'],
            [
                'name' => 'Ryan Gondel',
                'email' => 'ryan.gondel@gomsn.net',
                'phone' => '08120000005',
                'password' => Hash::make('password'),
                'employee_code' => 'KR-TK-005',
                'division' => 'Teknik',
                'is_active' => true,
            ]
        );
        $ryan->division = 'Teknik';
        $ryan->save();
        if (! $ryan->hasRole('karyawan')) {
            $ryan->assignRole('karyawan');
        }

        // 3. Overtime Data Definition
        $items = [
            // =========================================================================
            // BUDI (Teknik) - 6 Hari Lembur (Total Rp 230.000 persis Gambar 2)
            // =========================================================================
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-03',
                'start' => '17:00:00',
                'end' => '02:00:00',
                'durasi' => 540, // 9 jam
                'jenis' => 'Lembur',
                'alasan' => 'Progres konsorsium',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-03 17:30:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-04',
                'start' => '17:00:00',
                'end' => '03:00:00',
                'durasi' => 600, // 10 jam
                'jenis' => 'Lembur',
                'alasan' => 'Progres konsorsium',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-04 17:30:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-05',
                'start' => '17:00:00',
                'end' => '20:00:00',
                'durasi' => 180, // 3 jam
                'jenis' => 'Lembur',
                'alasan' => 'Perbaikan Sumarecon',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-05 17:30:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-06',
                'start' => '17:00:00',
                'end' => '01:00:00',
                'durasi' => 480, // 8 jam
                'jenis' => 'Lembur',
                'alasan' => 'Progres konsorsium gatsu',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-06 17:30:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-07',
                'start' => '09:00:00',
                'end' => '18:30:00',
                'durasi' => 570, // 9 jam 30 mnt
                'jenis' => 'Lembur',
                'alasan' => 'Penarikan 3 user baru di babakan Tarogong',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-07 10:00:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $budi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-08',
                'start' => '08:30:00',
                'end' => '15:00:00',
                'durasi' => 390, // 6 jam 30 mnt
                'jenis' => 'Lembur',
                'alasan' => 'Perbaikan user di babakan Tarogong',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-08 09:00:00',
                'rejection_reason' => null,
            ],

            // =========================================================================
            // DEDE RAHMAN (Teknik) - 1 Hari Lembur
            // =========================================================================
            [
                'user' => $dede,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-04',
                'start' => '17:00:00',
                'end' => '21:00:00',
                'durasi' => 240,
                'jenis' => 'Lembur',
                'alasan' => 'Penanganan jalur FO putus area Soreang',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-04 18:00:00',
                'rejection_reason' => null,
            ],

            // =========================================================================
            // OHANG (Teknik) - 1 Hari Lembur
            // =========================================================================
            [
                'user' => $ohang,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-05',
                'start' => '17:00:00',
                'end' => '20:00:00',
                'durasi' => 180,
                'jenis' => 'Lembur',
                'alasan' => 'Maintenance switch distribusi & OLT',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-05 18:00:00',
                'rejection_reason' => null,
            ],

            // =========================================================================
            // DUDI DURAHMAN (Teknik) - 1 Hari Lembur
            // =========================================================================
            [
                'user' => $dudi,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-06',
                'start' => '17:00:00',
                'end' => '21:00:00',
                'durasi' => 240,
                'jenis' => 'Lembur',
                'alasan' => 'Perapihan ODP dan splicing jalur Tarogong',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-06 18:00:00',
                'rejection_reason' => null,
            ],

            // =========================================================================
            // RYAN GONDEL (Teknik) - 5 Hari Lembur
            // =========================================================================
            [
                'user' => $ryan,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-02',
                'start' => '17:00:00',
                'end' => '21:00:00',
                'durasi' => 240,
                'jenis' => 'Lembur',
                'alasan' => 'Splicing 24 core di Joint Closure km 14',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-02 18:00:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $ryan,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-03',
                'start' => '17:00:00',
                'end' => '21:00:00',
                'durasi' => 240,
                'jenis' => 'Lembur',
                'alasan' => 'Troubleshooting redaman tinggi link FO segmen Banjaran',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-03 18:00:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $ryan,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-04',
                'start' => '17:00:00',
                'end' => '20:00:00',
                'durasi' => 180,
                'jenis' => 'Lembur',
                'alasan' => 'Setting OLT & integrasi port baru Kopo',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-04 18:00:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $ryan,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-05',
                'start' => '17:00:00',
                'end' => '21:00:00',
                'durasi' => 240,
                'jenis' => 'Lembur',
                'alasan' => 'Instalasi perangkat wireless backup BTS Sadu',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-05 18:00:00',
                'rejection_reason' => null,
            ],
            [
                'user' => $ryan,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-06',
                'start' => '17:00:00',
                'end' => '20:00:00',
                'durasi' => 180,
                'jenis' => 'Lembur',
                'alasan' => 'Restorasi sambungan backbone pasca cuaca buruk',
                'status' => 'approved_atasan',
                'approved_at' => '2026-11-06 18:00:00',
                'rejection_reason' => null,
            ],

            // =========================================================================
            // PENGAJUAN LEMBUR MANDIRI OLEH HRD (HRD bisa mengajukan untuk dirinya sendiri)
            // =========================================================================
            [
                'user' => $hrdUser,
                'atasan' => $atasanTeknik,
                'date' => '2026-11-09',
                'start' => '17:30:00',
                'end' => '20:30:00',
                'durasi' => 180,
                'jenis' => 'Lembur',
                'alasan' => 'Rekapitulasi berkas KPI dan pengajuan lembur bulanan seluruh divisi',
                'status' => 'pending',
                'approved_at' => null,
                'rejection_reason' => null,
            ],
        ];

        $seededCount = 0;
        foreach ($items as $entry) {
            $user = $entry['user'];
            if (! $user) {
                continue;
            }

            $date = $entry['date'];
            $startDt = Carbon::parse("{$date} {$entry['start']}");
            $endDt = Carbon::parse("{$date} {$entry['end']}");

            // Handle cross-midnight
            if ($endDt->lessThanOrEqualTo($startDt)) {
                $endDt->addDay();
            }

            $lemburRecord = Lembur::where('user_id', $user->id)->whereDate('tanggal', $date)->first();
            $nomorLembur = $lemburRecord?->nomor_lembur 
                ?? ('LBR-' . Carbon::parse($date)->format('Ymd') . '-' . strtoupper(substr(md5($user->id . $date . $entry['alasan'] . rand(1, 9999)), 0, 4)));

            $lemburAttributes = [
                'nomor_lembur' => $nomorLembur,
                'jenis_lembur' => $entry['jenis'],
                'user_id' => $user->id,
                'atasan_id' => $entry['atasan']->id,
                'tanggal' => $date,
                'jam_mulai' => $startDt,
                'jam_selesai' => $endDt,
                'durasi_menit' => $entry['durasi'],
                'alasan' => $entry['alasan'],
                'status' => $entry['status'],
                'approver_id' => in_array($entry['status'], ['approved_atasan', 'rejected', 'paid']) ? $entry['atasan']->id : null,
                'approved_at' => $entry['approved_at'] ? Carbon::parse($entry['approved_at']) : null,
                'rejection_reason' => $entry['rejection_reason'],
                'is_reconciled' => $entry['status'] !== 'pending',
                'reconciliation_flag' => 'NORMAL',
                'actual_clockout_at' => $endDt->copy()->addMinutes(5),
                'actual_diff_minutes' => 5,
                'paid_at' => isset($entry['paid_at']) ? Carbon::parse($entry['paid_at']) : null,
                'paid_by' => $entry['paid_by'] ?? null,
                'payment_reference' => $entry['payment_ref'] ?? null,
            ];

            if ($lemburRecord) {
                unset($lemburAttributes['nomor_lembur']);
                $lemburRecord->update($lemburAttributes);
            } else {
                Lembur::create($lemburAttributes);
            }

            // Attendance record for reconciliation
            $clockInTime = Carbon::parse("{$date} 08:00:00");
            $clockOutTime = $endDt->copy()->addMinutes(5);

            $attAttributes = [
                'shift_id' => $shiftPagi?->id ?? 1,
                'office_location_id' => $defaultOffice?->id ?? 1,
                'attendance_type' => 'office',
                'status' => 'present',
                'source' => 'web_portal',
                'clock_in_at' => $clockInTime,
                'clock_out_at' => $clockOutTime,
                'captured_at' => $clockInTime,
                'distance_m_in' => 10,
                'face_score' => 0.95,
                'liveness_score' => 0.98,
                'work_duration_minutes' => (int) $clockInTime->diffInMinutes($clockOutTime),
                'proposed_overtime_minutes' => $entry['durasi'],
                'notes' => 'Presensi hadir & lembur terverifikasi',
            ];

            $attRecord = Attendance::where('user_id', $user->id)->whereDate('date', $date)->first();
            if ($attRecord) {
                $attRecord->update($attAttributes);
            } else {
                Attendance::create(array_merge([
                    'user_id' => $user->id,
                    'date' => $date,
                ], $attAttributes));
            }

            $seededCount++;
        }

        $this->command?->info("Berhasil meng-seed {$seededCount} data lembur untuk pengujian dan approval lembur finance & HRD.");
    }
}
