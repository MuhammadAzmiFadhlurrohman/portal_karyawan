<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ImportUsersUnifiedCommand extends Command
{
    protected $signature = 'import:users-unified {file=users_unified2.sql} {--dry-run : Only simulate import without modifying DB}';

    protected $description = 'Import unified users from users_unified SQL dump into users table with proper roles and divisions';

    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");

            return 1;
        }

        $this->info("Membaca file SQL: {$filePath}...");
        $sqlContent = file_get_contents($filePath);

        $records = $this->parseSqlInserts($sqlContent);
        $this->info('Ditemukan ' . count($records) . ' baris user dari dump SQL.');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('[DRY-RUN MODE] Tidak ada perubahan data yang disimpan ke database.');
        }

        // Ensure all required Spatie roles exist
        $requiredRoles = [
            'admin', 'super_admin', 'ceo', 'hrd', 'finance', 'atasan',
            'atasan_noc', 'atasan_teknik', 'atasan_umum',
            'karyawan_noc', 'karyawan_teknik', 'karyawan_umum',
            'noc', 'teknisi', 'employee', 'manager_teknisi',
        ];

        foreach ($requiredRoles as $rName) {
            Role::findOrCreate($rName, 'web');
        }

        $imported = 0;
        $updated = 0;
        $tableData = [];

        foreach ($records as $row) {
            $rawId = (int) $row['id'];
            $rawUsername = $row['username'];
            $rawPhone = $row['nomor_telepon'];
            $rawName = $row['nama_lengkap'];
            $rawStatus = $row['status'];
            $rawFoto = $row['foto'];
            $rawPassword = $row['password'];
            $rawRole = $row['role'];
            $rawDivisi = $row['divisi'];
            $rawNoRekening = $row['no_rekening'];
            $rawBank = $row['bank'];
            $rawAtasNama = $row['atas_nama'];
            $rawKodeKaryawan = $row['kode_karyawan'];
            $rawEmail = $row['email'];

            // 1. Resolve Name
            $name = ! empty(trim($rawName ?? '')) ? trim($rawName) : trim($rawUsername ?? 'Karyawan MSN');

            // 2. Resolve Employee Code
            $employeeCode = trim($rawKodeKaryawan ?? '');
            if (empty($employeeCode) || $employeeCode === '-' || $employeeCode === '0') {
                $employeeCode = 'KR' . str_pad($rawId, 5, '0', STR_PAD_LEFT);
            }

            // 3. Resolve Username
            $cleanUsername = trim($rawUsername ?? '');
            if (empty($cleanUsername)) {
                $cleanUsername = 'user_' . strtolower(Str::slug($name, '_'));
            }

            // 4. Resolve Email
            $email = trim($rawEmail ?? '');
            if (empty($email) || $email === '-' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $slug = Str::slug($cleanUsername, '.');
                $email = strtolower($slug) . '@ptmsn.co.id';
            }

            // 5. Resolve Phone
            $phone = trim($rawPhone ?? '');
            if (empty($phone) || $phone === '-' || strlen($phone) < 6) {
                $phone = '0899' . str_pad($rawId, 8, '0', STR_PAD_LEFT);
            }

            // 6. Resolve Division, Position, and Roles
            [$division, $position, $roles] = $this->determineDivisionAndRoles($rawRole, $rawDivisi, $name);

            // 7. Check if user already exists BY EMPLOYEE_CODE
            $existingUser = User::query()->where('employee_code', $employeeCode)->first();

            // If username conflicts with another user (different employee_code), make it unique
            $conflictUser = User::query()->where('username', $cleanUsername)->first();
            if ($conflictUser && (! $existingUser || $conflictUser->id !== $existingUser->id)) {
                $cleanUsername = strtolower(Str::slug($cleanUsername, '_')) . '_' . $rawId;
            }

            // If email conflicts with another user (different employee_code), make it unique
            $conflictEmail = User::query()->where('email', $email)->first();
            if ($conflictEmail && (! $existingUser || $conflictEmail->id !== $existingUser->id)) {
                $email = strtolower(Str::slug($cleanUsername, '.')) . '.' . $rawId . '@ptmsn.co.id';
            }

            // If phone conflicts with another user (different employee_code), make it unique
            $conflictPhone = User::query()->where('phone', $phone)->first();
            if ($conflictPhone && (! $existingUser || $conflictPhone->id !== $existingUser->id)) {
                $phone = '0899' . str_pad($rawId, 8, '0', STR_PAD_LEFT);
            }

            $userData = [
                'name' => $name,
                'username' => $cleanUsername,
                'employee_code' => $employeeCode,
                'email' => $email,
                'phone' => $phone,
                'division' => $division,
                'position' => $position,
                'bank' => ! empty($rawBank) ? trim($rawBank) : null,
                'no_rekening' => ! empty($rawNoRekening) ? trim($rawNoRekening) : null,
                'atas_nama' => ! empty($rawAtasNama) ? trim($rawAtasNama) : null,
                'foto' => ! empty($rawFoto) ? trim($rawFoto) : null,
                'is_active' => ($rawStatus !== 'Inactive'),
                'password' => 'MsnPortal#2026',
            ];

            if (! $dryRun) {
                if ($existingUser) {
                    $existingUser->update($userData);
                    if (! empty($rawPassword) && str_starts_with($rawPassword, '$2y$')) {
                        DB::table('users')->where('id', $existingUser->id)->update(['password' => $rawPassword]);
                    }
                    $existingUser->syncRoles($roles);
                    $updated++;
                    $actionStatus = 'Updated';
                    $targetId = $existingUser->id;
                } else {
                    $newUser = User::query()->create($userData);
                    if (! empty($rawPassword) && str_starts_with($rawPassword, '$2y$')) {
                        DB::table('users')->where('id', $newUser->id)->update(['password' => $rawPassword]);
                    }
                    $newUser->syncRoles($roles);
                    $imported++;
                    $actionStatus = 'Created';
                    $targetId = $newUser->id;
                }
            } else {
                $actionStatus = $existingUser ? 'Would Update' : 'Would Create';
                $targetId = $existingUser ? $existingUser->id : $rawId;
                if ($existingUser) {
                    $updated++;
                } else {
                    $imported++;
                }
            }

            $tableData[] = [
                $targetId,
                $employeeCode,
                $cleanUsername,
                $name,
                $division,
                implode(', ', $roles),
                $actionStatus,
            ];
        }

        $this->table(
            ['ID', 'Kode Karyawan', 'Username', 'Nama Lengkap', 'Divisi', 'Roles', 'Status'],
            $tableData
        );

        $this->info("Selesai! {$imported} user dibuat baru, {$updated} user diperbarui.");

        return 0;
    }

    /**
     * Parse INSERT statements from SQL dump into array of associative rows.
     */
    protected function parseSqlInserts(string $sql): array
    {
        $rows = [];
        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $line = trim($line);
            if (! str_starts_with($line, 'INSERT INTO `users_unified` VALUES')) {
                continue;
            }

            // Extract values inside parenthesis: VALUES ( ... );
            if (preg_match('/VALUES\s*\((.*)\);/is', $line, $matches)) {
                $valStr = $matches[1];
                $values = str_getcsv($valStr, ',', "'", "\\");

                // Trim whitespace and handle NULLs and single quotes
                $values = array_map(function ($val) {
                    if ($val === null) {
                        return null;
                    }
                    $val = trim($val);
                    if (strtoupper($val) === 'NULL') {
                        return null;
                    }
                    if (str_starts_with($val, "'") && str_ends_with($val, "'") && strlen($val) >= 2) {
                        $val = substr($val, 1, -1);
                    }

                    return $val;
                }, $values);

                if (count($values) >= 14) {
                    $rows[] = [
                        'id' => $values[0] ?? null,
                        'username' => $values[1] ?? null,
                        'nomor_telepon' => $values[2] ?? null,
                        'nama_lengkap' => $values[3] ?? null,
                        'status' => $values[4] ?? 'Available',
                        'foto' => $values[5] ?? null,
                        'password' => $values[6] ?? null,
                        'role' => $values[7] ?? 'karyawan',
                        'divisi' => $values[8] ?? null,
                        'no_rekening' => $values[9] ?? null,
                        'bank' => $values[10] ?? null,
                        'atas_nama' => $values[11] ?? null,
                        'kode_karyawan' => $values[12] ?? null,
                        'email' => $values[13] ?? null,
                        'auth_token' => $values[14] ?? null,
                        'created_at' => $values[15] ?? null,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * Map legacy role & divisi to system division, position, and Spatie roles.
     */
    protected function determineDivisionAndRoles(?string $rawRole, ?string $rawDivisi, string $name): array
    {
        $roleNorm = strtolower(trim($rawRole ?? ''));
        $divisiNorm = strtolower(trim($rawDivisi ?? ''));
        $nameLower = strtolower($name);

        $roles = [];

        // CEO / Direktur
        if ($roleNorm === 'ceo' || $divisiNorm === 'direktur' || str_contains($nameLower, 'yudiana')) {
            $roles = ['ceo'];
            return ['Direksi', 'Chief Executive Officer', $roles];
        }

        // Admin
        if ($roleNorm === 'admin' || str_contains($nameLower, 'admin') || $divisiNorm === 'aplikasi') {
            $roles = ['admin', 'super_admin'];
            return ['IT & Sistem', 'Administrator Sistem', $roles];
        }

        // HRD
        if ($roleNorm === 'hrd' || str_contains($nameLower, 'hrd')) {
            $roles = ['hrd'];
            return ['HRD', 'Human Resources Development', $roles];
        }

        // Finance
        if ($roleNorm === 'finance' || $divisiNorm === 'finance' || str_contains($nameLower, 'finance')) {
            $roles = ['finance'];
            return ['Finance', 'Finance & Akuntansi', $roles];
        }

        // Atasan (Supervisors)
        if ($roleNorm === 'atasan') {
            $roles[] = 'atasan';
            if (str_contains($divisiNorm, 'noc')) {
                $roles[] = 'atasan_noc';
                return ['NOC', 'Supervisor NOC', $roles];
            }
            if (str_contains($divisiNorm, 'teknik')) {
                $roles[] = 'atasan_teknik';
                $roles[] = 'manager_teknisi';
                return ['Teknik', 'Supervisor Teknik', $roles];
            }
            if (str_contains($divisiNorm, 'umum')) {
                $roles[] = 'atasan_umum';
                return ['Umum', 'Supervisor Umum', $roles];
            }

            return ['Operasional', 'Supervisor Operasional', $roles];
        }

        // Karyawan NOC
        if ($roleNorm === 'noc' || str_contains($divisiNorm, 'noc')) {
            $roles = ['karyawan_noc', 'noc'];
            return ['NOC', 'Staf NOC', $roles];
        }

        // Karyawan Teknik
        if (str_contains($divisiNorm, 'teknik')) {
            $roles = ['karyawan_teknik', 'teknisi'];
            return ['Teknik', 'Teknisi Lapangan', $roles];
        }

        // Karyawan Umum
        if (str_contains($divisiNorm, 'umum')) {
            $roles = ['karyawan_umum'];
            return ['Umum', 'Staf Umum', $roles];
        }

        // Default Karyawan
        $roles = ['karyawan_teknik', 'teknisi'];
        return ['Teknik', 'Teknisi Lapangan', $roles];
    }
}
