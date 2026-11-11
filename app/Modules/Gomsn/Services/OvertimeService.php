<?php

namespace App\Modules\Gomsn\Services;

use App\Models\Attendance;
use App\Models\Lembur;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

class OvertimeService
{
    /**
     * Submit overtime request (§8.2).
     */
    public function createOvertime(
        User $user,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        string $alasan,
        ?string $jenisLembur = null,
        bool $requireAttendance = false
    ): Lembur {
        if (empty(trim($alasan))) {
            throw new InvalidArgumentException('Alasan lembur wajib diisi.');
        }

        $date = Carbon::parse($tanggal)->toDateString();

        if ($requireAttendance) {
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $date)
                ->where(function ($q) {
                    $q->whereIn('status', ['present', 'late'])
                      ->orWhereNotNull('clock_in_at');
                })
                ->first();

            if (! $attendance) {
                throw new Exception('Tidak ditemukan absensi pada tanggal tersebut. Pastikan Anda sudah melakukan absensi dengan status Hadir/Tepat Waktu.');
            }
        }

        $start = Carbon::parse("{$date} {$jamMulai}");
        $end = Carbon::parse("{$date} {$jamSelesai}");

        // Handle cross-midnight overtime (+24 hours) (§8.2)
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $durasiMenit = (int) $start->diffInMinutes($end);

        // Max 4 hours (240 minutes) per day
        if ($durasiMenit > 240) {
            throw new Exception('Batas lembur per hari maksimal adalah 4 jam (240 menit).');
        }

        // Retroactive limit: max 3 days in the past
        if (Carbon::parse($date)->lessThan(Carbon::now()->subDays(3)->startOfDay())) {
            throw new Exception('Pengajuan lembur retroaktif dibatasi maksimal 3 hari ke belakang.');
        }

        // Overlapping check (§8.2)
        $hasOverlap = Lembur::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved_atasan', 'paid'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('jam_mulai', [$start, $end])
                    ->orWhereBetween('jam_selesai', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('jam_mulai', '<=', $start)
                            ->where('jam_selesai', '>=', $end);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            throw new Exception('Jadwal lembur bertabrakan dengan pengajuan lembur Anda yang lain.');
        }

        $nomor = 'LBR-'.Carbon::now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(2)));

        $targetAtasan = $this->resolveSupervisorForUser($user);

        $lembur = Lembur::query()->create([
            'nomor_lembur' => $nomor,
            'jenis_lembur' => $jenisLembur ?? 'Lembur Hari Kerja',
            'user_id' => $user->id,
            'atasan_id' => $targetAtasan?->id,
            'tanggal' => $date,
            'jam_mulai' => $start,
            'jam_selesai' => $end,
            'durasi_menit' => $durasiMenit,
            'alasan' => $alasan,
            'status' => 'pending',
        ]);

        AuditLogger::record(
            action: 'lembur.create',
            auditable: $lembur,
            newValues: ['nomor' => $nomor, 'durasi_menit' => $durasiMenit, 'atasan_id' => $targetAtasan?->id],
            reason: "Pengajuan lembur dibuat oleh {$user->name} untuk atasan " . ($targetAtasan?->name ?? 'Divisi'),
            actorId: $user->id
        );

        return $lembur;
    }

    /**
     * Update overtime by supervisor or authorized user (§8.2).
     */
    public function updateOvertime(
        Lembur $lembur,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        string $alasan,
        ?string $jenisLembur,
        User $actor
    ): Lembur {
        if (empty(trim($alasan))) {
            throw new InvalidArgumentException('Alasan lembur wajib diisi.');
        }

        $date = Carbon::parse($tanggal)->toDateString();
        $start = Carbon::parse("{$date} {$jamMulai}");
        $end = Carbon::parse("{$date} {$jamSelesai}");

        // Handle cross-midnight overtime (+24 hours) (§8.2)
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $durasiMenit = (int) $start->diffInMinutes($end);

        // Max 4 hours (240 minutes) per day
        if ($durasiMenit > 240) {
            throw new Exception('Batas lembur per hari maksimal adalah 4 jam (240 menit).');
        }

        $oldValues = [
            'tanggal' => $lembur->tanggal?->format('Y-m-d'),
            'jam_mulai' => $lembur->jam_mulai?->format('H:i'),
            'jam_selesai' => $lembur->jam_selesai?->format('H:i'),
            'durasi_menit' => $lembur->durasi_menit,
            'alasan' => $lembur->alasan,
            'jenis_lembur' => $lembur->jenis_lembur,
        ];

        $lembur->tanggal = $date;
        $lembur->jam_mulai = $start;
        $lembur->jam_selesai = $end;
        $lembur->durasi_menit = $durasiMenit;
        $lembur->alasan = $alasan;
        if (! empty($jenisLembur)) {
            $lembur->jenis_lembur = $jenisLembur;
        }
        $lembur->save();

        if ($lembur->is_reconciled) {
            $this->reconcileWithAttendance($lembur);
        }

        AuditLogger::record(
            action: 'lembur.update_by_atasan',
            auditable: $lembur,
            oldValues: $oldValues,
            newValues: [
                'tanggal' => $date,
                'jam_mulai' => $start->format('H:i'),
                'jam_selesai' => $end->format('H:i'),
                'durasi_menit' => $durasiMenit,
                'alasan' => $alasan,
                'jenis_lembur' => $lembur->jenis_lembur,
            ],
            reason: "Pengajuan lembur {$lembur->nomor_lembur} disesuaikan/diedit oleh atasan {$actor->name}",
            actorId: $actor->id
        );

        return $lembur;
    }

    /**
     * Resolve the appropriate supervisor for a user based on their division (§8.2).
     */
    public function resolveSupervisorForUser(User $user): ?User
    {
        // 1. If user has explicit supervisor_id and supervisor is active, use that
        if ($user->supervisor_id) {
            $sup = User::where('id', $user->supervisor_id)->where('is_active', true)->first();
            if ($sup) {
                return $sup;
            }
        }

        $division = trim($user->division ?? '');

        // 2. Find Atasan with matching division
        if (! empty($division)) {
            $roleName = match (strtolower($division)) {
                'teknik', 'lapangan', 'maintenance' => 'atasan_teknik',
                'noc', 'network', 'jaringan' => 'atasan_noc',
                'umum', 'ga', 'general' => 'atasan_umum',
                default => null,
            };

            if ($roleName) {
                $supervisorByRole = User::where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->whereHas('roles', function ($q) use ($roleName) {
                        $q->where('name', $roleName);
                    })
                    ->first();

                if ($supervisorByRole) {
                    return $supervisorByRole;
                }
            }

            $supervisorByDivision = User::where('is_active', true)
                ->where('id', '!=', $user->id)
                ->whereRaw('LOWER(division) = ?', [strtolower($division)])
                ->where(function ($q) {
                    $q->whereHas('permissions', function ($p) {
                        $p->where('name', 'gomsn.lembur.approve');
                    })->orWhereHas('roles', function ($r) {
                        $r->whereIn('name', ['atasan', 'atasan_teknik', 'atasan_noc', 'atasan_umum', 'manager_teknisi']);
                    });
                })
                ->first();

            if ($supervisorByDivision) {
                return $supervisorByDivision;
            }
        }

        // 3. Fallback: any active atasan
        return User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereHas('permissions', function ($p) {
                    $p->where('name', 'gomsn.lembur.approve');
                })->orWhereHas('roles', function ($r) {
                    $r->whereIn('name', ['atasan', 'atasan_teknik', 'atasan_noc', 'atasan_umum', 'manager_teknisi', 'super_admin']);
                });
            })
            ->first();
    }

    /**
     * Supervisor approval for overtime (§8.2).
     */
    public function approveSupervisor(Lembur $lembur, User $supervisor): Lembur
    {
        if ($lembur->user_id === $supervisor->id) {
            throw new Exception('Pemisahan tugas: Anda tidak boleh menyetujui lembur Anda sendiri.');
        }

        if ($lembur->status !== 'pending') {
            throw new Exception("Lembur berstatus {$lembur->status} tidak dapat disetujui.");
        }

        $lembur->status = 'approved_atasan';
        $lembur->approver_id = $supervisor->id;
        $lembur->approved_at = Carbon::now();
        $lembur->save();

        // Perform attendance reconciliation (§8.2)
        $this->reconcileWithAttendance($lembur);

        AuditLogger::record(
            action: 'lembur.approve',
            auditable: $lembur,
            newValues: ['status' => 'approved_atasan', 'approver' => $supervisor->name],
            reason: 'Lembur disetujui Atasan',
            actorId: $supervisor->id
        );

        return $lembur;
    }

    /**
     * Supervisor rejection for overtime (§8.2).
     */
    public function rejectSupervisor(Lembur $lembur, User $supervisor, ?string $reason = null): Lembur
    {
        if ($lembur->user_id === $supervisor->id) {
            throw new Exception('Pemisahan tugas: Anda tidak boleh menolak lembur Anda sendiri.');
        }

        if ($lembur->status !== 'pending') {
            throw new Exception("Lembur berstatus {$lembur->status} tidak dapat ditolak.");
        }

        $lembur->status = 'rejected';
        $lembur->approver_id = $supervisor->id;
        $lembur->rejection_reason = $reason ?? 'Pengajuan lembur ditolak oleh atasan.';
        $lembur->save();

        AuditLogger::record(
            action: 'lembur.reject',
            auditable: $lembur,
            newValues: ['status' => 'rejected', 'approver' => $supervisor->name, 'reason' => $reason],
            reason: 'Lembur ditolak Atasan',
            actorId: $supervisor->id
        );

        return $lembur;
    }

    /**
     * Reconcile overtime with attendance clock-out data (§8.2).
     */
    public function reconcileWithAttendance(Lembur $lembur): Lembur
    {
        $attendance = Attendance::query()
            ->where('user_id', $lembur->user_id)
            ->whereDate('date', $lembur->tanggal)
            ->first();

        $lembur->is_reconciled = true;

        $clockOutRaw = $attendance?->clock_out_at ?? $attendance?->clock_out;

        if (! $attendance || ! $clockOutRaw) {
            $lembur->reconciliation_flag = 'DISCREPANCY_OVER_TOLERANCE';
            $lembur->actual_clockout_at = null;
            $lembur->actual_diff_minutes = $lembur->durasi_menit;
        } else {
            $clockOut = Carbon::parse($clockOutRaw);
            $lemburEnd = Carbon::parse($lembur->jam_selesai);
            $diffMinutes = (int) abs($clockOut->diffInMinutes($lemburEnd));

            $lembur->actual_clockout_at = $clockOut;
            $lembur->actual_diff_minutes = $diffMinutes;

            // Flag if discrepancy > 30 minutes for Finance review (§8.2)
            if ($diffMinutes > 30) {
                $lembur->reconciliation_flag = 'DISCREPANCY_OVER_TOLERANCE';
            } else {
                $lembur->reconciliation_flag = 'NORMAL';
            }
        }

        $lembur->save();

        return $lembur;
    }

    /**
     * Mark overtime as paid by Finance (§8.2).
     */
    public function payOvertime(Lembur $lembur, User $financeUser, string $reference): Lembur
    {
        if ($lembur->status !== 'approved_atasan') {
            throw new Exception('Hanya lembur yang sudah disetujui atasan yang dapat dibayarkan.');
        }

        $lembur->status = 'paid';
        $lembur->paid_at = Carbon::now();
        $lembur->paid_by = $financeUser->id;
        $lembur->payment_reference = $reference;
        $lembur->save();

        AuditLogger::record(
            action: 'lembur.pay',
            auditable: $lembur,
            newValues: ['status' => 'paid', 'payment_reference' => $reference],
            reason: "Lembur dibayarkan oleh Finance {$financeUser->name}",
            actorId: $financeUser->id
        );

        return $lembur;
    }
}
