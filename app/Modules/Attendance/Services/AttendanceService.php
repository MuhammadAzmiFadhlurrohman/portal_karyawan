<?php

namespace App\Modules\Attendance\Services;

use App\Models\Attendance;
use App\Models\FaceAttemptToken;
use App\Models\FaceTemplate;
use App\Models\MonthlySchedule;
use App\Models\OfficeLocation;
use App\Models\Shift;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private readonly FaceRecognitionClient $faceClient,
        private readonly GeofenceService $geofenceService,
    ) {}

    /**
     * Enroll user biometric template with UU PDP No. 27/2022 explicit consent (§5.1, §10.2).
     *
     * @param  array<int, string>  $posesBase64
     */
    public function enrollUserFace(User $user, array $posesBase64, bool $consentGiven, string $ip): FaceTemplate
    {
        if (! $consentGiven) {
            throw ValidationException::withMessages([
                'consent' => 'Persetujuan eksplisit pemrosesan data biometrik (UU PDP No. 27/2022) wajib dicentang (§10.2).',
            ]);
        }

        $result = $this->faceClient->enroll($user, $posesBase64);

        $referencePath = 'biometrics/ref_'.$user->id.'_'.Str::random(24).'.txt';
        Storage::disk('private')->put($referencePath, $posesBase64[0]);

        $template = FaceTemplate::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'encrypted_embedding' => $result['embedding'],
                'primary_photo_hash' => $result['primary_hash'],
                'reference_photo_path' => $referencePath,
                'model_version' => $result['model_version'],
                'poses_count' => count($posesBase64),
                'quality_score' => $result['quality_score'],
                'consent_at' => now(),
                'consent_ip' => $ip,
                'enrolled_at' => now(),
            ]
        );

        Cache::forget($this->failureCacheKey($user->id));

        AuditLogger::record(
            action: 'biometric.face_enrolled',
            auditable: $template,
            newValues: [
                'user_id' => $user->id,
                'model_version' => $result['model_version'],
                'poses_count' => count($posesBase64),
                'consent_at' => $template->consent_at->toIso8601String(),
            ],
            reason: 'Pendaftaran wajah 3 pose dengan persetujuan UU PDP No. 27/2022 (§10.2)',
            actorId: $user->id
        );

        return $template;
    }

    /**
     * Reset Wajah: deletes template & stored photos, logs to audit_logs, requires re-enrollment (AC-12).
     */
    public function resetUserFace(User $targetUser, string $reason, int $actorId): void
    {
        $template = FaceTemplate::query()->where('user_id', $targetUser->id)->first();

        $deletedFiles = [];
        if ($template && $template->reference_photo_path) {
            Storage::disk('private')->delete($template->reference_photo_path);
            $deletedFiles[] = $template->reference_photo_path;
        }

        $attendancesWithPhotos = Attendance::query()
            ->where('user_id', $targetUser->id)
            ->where(function ($q): void {
                $q->whereNotNull('photo_in_primary_path')
                    ->orWhereNotNull('photo_in_challenge_path')
                    ->orWhereNotNull('photo_out_path');
            })
            ->get();

        foreach ($attendancesWithPhotos as $att) {
            foreach (['photo_in_primary_path', 'photo_in_challenge_path', 'photo_out_path'] as $col) {
                $path = $att->{$col};
                if ($path) {
                    Storage::disk('private')->delete($path);
                    $deletedFiles[] = $path;
                }
            }
            $att->update([
                'photo_in_primary_path' => null,
                'photo_in_challenge_path' => null,
                'photo_out_path' => null,
            ]);
        }

        if ($template) {
            $template->delete();
        }

        $this->faceClient->deleteRemoteTemplate($targetUser->id);
        Cache::forget($this->failureCacheKey($targetUser->id));

        AuditLogger::record(
            action: 'biometric.face_reset',
            auditable: $targetUser,
            oldValues: [
                'had_template' => $template !== null,
                'purged_files_count' => count($deletedFiles),
            ],
            newValues: [
                'template_deleted' => true,
                'requires_reenrollment' => true,
            ],
            reason: $reason,
            actorId: $actorId
        );
    }

    /**
     * Stage 1 Clock-In: Validate real-time face detection & issue single-use 2-minute attempt_token (§5.1).
     */
    public function issueAttemptToken(User $user, string $sampleImageBase64): FaceAttemptToken
    {
        $detection = $this->faceClient->detect($sampleImageBase64, $user);

        $challenges = ['smile', 'blink', 'turn_left', 'turn_right'];
        $challenge = $challenges[random_int(0, count($challenges) - 1)];

        return FaceAttemptToken::query()->create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'challenge_type' => $challenge,
            'detected_quality' => $detection['quality']['overall'] ?? 0.90,
            'expires_at' => now()->addMinutes(2),
        ]);
    }

    /**
     * Stage 2 Clock-In: Server-side re-verification of face, liveness, attempt_token, and geofence (§5.1, AC-01, AC-02).
     *
     * @param  array<string, mixed>  $data
     */
    public function clockIn(User $user, array $data): Attendance
    {
        $officeTime = now('Asia/Jakarta');
        $todayDate = $officeTime->toDateString();

        $existing = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'attendance' => 'Anda sudah melakukan clock-in pada tanggal '.$todayDate.'.',
            ]);
        }

        $attendanceType = (string) ($data['attendance_type'] ?? 'office');
        if (in_array($attendanceType, ['field', 'remote'], true) && empty(trim((string) ($data['notes'] ?? '')))) {
            throw ValidationException::withMessages([
                'notes' => "Kehadiran tipe `{$attendanceType}` wajib menyertakan catatan keterangan (§5.1).",
            ]);
        }

        $office = isset($data['office_location_id'])
            ? OfficeLocation::query()->where('is_active', true)->findOrFail($data['office_location_id'])
            : OfficeLocation::query()->where('is_active', true)->firstOrFail();

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $gpsAccuracy = (float) ($data['gps_accuracy'] ?? 10.0);
        $isMockLocation = (bool) ($data['is_mock_location'] ?? false);

        // Evaluate geofence first (rejects out-of-radius office attendance immediately — AC-01)
        $geoResult = $this->geofenceService->evaluateLocation(
            office: $office,
            lat: $lat,
            lng: $lng,
            gpsAccuracyMeters: $gpsAccuracy,
            isMockLocation: $isMockLocation,
            attendanceType: $attendanceType
        );

        $maxFailedAttempts = (int) SystemSettingService::get('face.max_failed_attempts', 3);
        $currentFailures = (int) Cache::get($this->failureCacheKey($user->id), 0);
        $useManualFallback = (bool) ($data['use_manual_fallback'] ?? false);

        $faceScore = null;
        $livenessScore = null;
        $source = 'face';
        $status = 'present';

        if ($useManualFallback) {
            if ($currentFailures < $maxFailedAttempts) {
                throw ValidationException::withMessages([
                    'use_manual_fallback' => "Jalur manual hanya terbuka setelah {$maxFailedAttempts} kali gagal verifikasi wajah berturut-turut (§5.1).",
                ]);
            }
            if (empty(trim((string) ($data['notes'] ?? '')))) {
                throw ValidationException::withMessages([
                    'notes' => 'Jalur manual wajib menyertakan alasan untuk ditinjau HRD (§5.1).',
                ]);
            }
            $source = 'manual';
            $status = 'pending_review';
        } else {
            $tokenStr = (string) ($data['attempt_token'] ?? '');
            $attemptToken = FaceAttemptToken::query()
                ->where('user_id', $user->id)
                ->where('token', $tokenStr)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();

            if (! $attemptToken) {
                throw ValidationException::withMessages([
                    'attempt_token' => 'Token percobaan absensi tahap 1 tidak valid atau sudah kedaluwarsa (batas 2 menit, sekali pakai — §5.1).',
                ]);
            }

            $attemptToken->update(['used_at' => now()]);

            $template = FaceTemplate::query()->where('user_id', $user->id)->first();
            if (! $template) {
                throw ValidationException::withMessages([
                    'face' => 'Anda belum mendaftarkan biometrik wajah. Silakan lakukan Pendaftaran Wajah 3 Pose terlebih dahulu (§5.1).',
                ]);
            }

            $verifyResult = $this->faceClient->verifyOneToOne(
                user: $user,
                template: $template,
                primaryImageBase64: (string) $data['primary_image'],
                challengeImageBase64: (string) $data['challenge_image']
            );

            if (! $verifyResult['match']) {
                $currentFailures++;
                Cache::put($this->failureCacheKey($user->id), $currentFailures, now()->addHours(6));

                AuditLogger::record(
                    action: 'attendance.face_verify_failed',
                    auditable: $user,
                    newValues: [
                        'score' => $verifyResult['score'],
                        'consecutive_failures' => $currentFailures,
                    ],
                    actorId: $user->id
                );

                if ($currentFailures >= $maxFailedAttempts) {
                    throw ValidationException::withMessages([
                        'face' => "Verifikasi wajah gagal {$currentFailures}x berturut-turut (skor {$verifyResult['score']}). Anda kini diarahkan ke Jalur Manual (status pending_review HRD) (§5.1).",
                    ]);
                }

                throw ValidationException::withMessages([
                    'face' => "Verifikasi wajah 1:1 tidak cocok (skor {$verifyResult['score']}). Percobaan gagal ke-{$currentFailures} dari {$maxFailedAttempts}.",
                ]);
            }

            Cache::forget($this->failureCacheKey($user->id));
            $faceScore = $verifyResult['score'];
            $livenessScore = $verifyResult['liveness_score'];
        }

        // Resolve today's shift and calculate late_minutes (§5.1, §5.2)
        $schedule = MonthlySchedule::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();

        $shift = $schedule ? $schedule->shift : Shift::query()->where('code', 'P')->first();
        $lateMinutes = $this->calculateLateMinutes($officeTime, $shift, $attendanceType);

        if ($status !== 'pending_review' && $lateMinutes > 0) {
            $status = 'late';
        }

        $primaryPath = 'attendances/in_'.$user->id.'_'.Str::random(20).'.txt';
        $challengePath = 'attendances/ch_'.$user->id.'_'.Str::random(20).'.txt';
        Storage::disk('private')->put($primaryPath, (string) ($data['primary_image'] ?? 'MANUAL'));
        Storage::disk('private')->put($challengePath, (string) ($data['challenge_image'] ?? 'MANUAL'));

        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'date' => $todayDate,
            'shift_id' => $shift?->id,
            'office_location_id' => $office->id,
            'attendance_type' => $attendanceType,
            'status' => $status,
            'source' => $source,
            'clock_in_at' => now(),
            'captured_at' => now(),
            'lat_in' => $lat,
            'lng_in' => $lng,
            'gps_accuracy_in' => $gpsAccuracy,
            'distance_m_in' => $geoResult['distance_m'],
            'is_mock_location' => $isMockLocation,
            'face_score' => $faceScore,
            'liveness_score' => $livenessScore,
            'is_flagged' => $geoResult['is_flagged'],
            'flag_reason' => $geoResult['flag_reason'],
            'late_minutes' => $lateMinutes,
            'notes' => $data['notes'] ?? null,
            'ticket_reference' => $data['ticket_reference'] ?? null,
            'photo_in_primary_path' => $primaryPath,
            'photo_in_challenge_path' => $challengePath,
            'client_uuid' => $data['client_uuid'] ?? null,
        ]);

        AuditLogger::record(
            action: 'attendance.clock_in',
            auditable: $attendance,
            newValues: [
                'status' => $attendance->status,
                'source' => $attendance->source,
                'attendance_type' => $attendance->attendance_type,
                'distance_m' => $attendance->distance_m_in,
                'is_flagged' => $attendance->is_flagged,
                'late_minutes' => $attendance->late_minutes,
            ],
            actorId: $user->id
        );

        return $attendance;
    }

    /**
     * Clock-Out & calculate net work duration + proposed overtime (> 8 hours net, §5.1).
     *
     * @param  array<string, mixed>  $data
     */
    public function clockOut(User $user, array $data = []): Attendance
    {
        $todayDate = now('Asia/Jakarta')->toDateString();

        $attendance = Attendance::query()
            ->with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->whereNull('clock_out_at')
            ->first();

        if (! $attendance) {
            throw ValidationException::withMessages([
                'attendance' => 'Tidak ditemukan data clock-in aktif hari ini untuk di-clock-out.',
            ]);
        }

        $clockOutTime = now();
        $grossMinutes = $attendance->clock_in_at
            ? (int) max(0, $attendance->clock_in_at->diffInMinutes($clockOutTime))
            : 0;

        $breakMinutes = $attendance->shift ? $attendance->shift->break_minutes : 60;
        $netWorkMinutes = max(0, $grossMinutes - $breakMinutes);
        $proposedOvertime = max(0, $netWorkMinutes - 480); // > 8 hours net work (§5.1)

        $attendance->update([
            'clock_out_at' => $clockOutTime,
            'lat_out' => isset($data['lat']) ? (float) $data['lat'] : $attendance->lat_in,
            'lng_out' => isset($data['lng']) ? (float) $data['lng'] : $attendance->lng_in,
            'gps_accuracy_out' => isset($data['gps_accuracy']) ? (float) $data['gps_accuracy'] : $attendance->gps_accuracy_in,
            'work_duration_minutes' => $netWorkMinutes,
            'proposed_overtime_minutes' => $proposedOvertime,
        ]);

        AuditLogger::record(
            action: 'attendance.clock_out',
            auditable: $attendance,
            newValues: [
                'work_duration_minutes' => $netWorkMinutes,
                'proposed_overtime_minutes' => $proposedOvertime,
            ],
            actorId: $user->id
        );

        return $attendance;
    }

    /**
     * Idempotent Offline Attendance Synchronization (§5.1, AC-03).
     *
     * @param  array<string, mixed>  $data
     */
    public function syncOfflineAttendance(User $user, array $data): Attendance
    {
        $clientUuid = trim((string) ($data['client_uuid'] ?? ''));
        if ($clientUuid === '') {
            throw ValidationException::withMessages([
                'client_uuid' => 'Sinkronisasi offline wajib menyertakan client_uuid unik (§5.1).',
            ]);
        }

        // Idempotency guarantee (AC-03): if client_uuid already exists, return it without duplicate creation
        $existingByUuid = Attendance::query()->where('client_uuid', $clientUuid)->first();
        if ($existingByUuid) {
            return $existingByUuid;
        }

        $capturedAt = isset($data['captured_at']) ? Carbon::parse((string) $data['captured_at']) : now();
        $capturedDate = $capturedAt->copy()->timezone('Asia/Jakarta')->toDateString();

        // Also check unique (user_id, date)
        $existingByDate = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $capturedDate)
            ->first();

        if ($existingByDate) {
            return $existingByDate;
        }

        $template = FaceTemplate::query()->where('user_id', $user->id)->first();
        if (! $template) {
            throw ValidationException::withMessages([
                'face' => 'Template biometrik pengguna belum terdaftar saat sinkronisasi offline.',
            ]);
        }

        $verifyResult = $this->faceClient->verifyOneToOne(
            user: $user,
            template: $template,
            primaryImageBase64: (string) $data['primary_image'],
            challengeImageBase64: (string) $data['challenge_image']
        );

        if (! $verifyResult['match']) {
            throw ValidationException::withMessages([
                'face' => 'Verifikasi wajah server gagal pada data sinkronisasi offline.',
            ]);
        }

        $office = isset($data['office_location_id'])
            ? OfficeLocation::query()->where('is_active', true)->findOrFail($data['office_location_id'])
            : OfficeLocation::query()->where('is_active', true)->firstOrFail();

        $attendanceType = (string) ($data['attendance_type'] ?? 'office');
        $geoResult = $this->geofenceService->evaluateLocation(
            office: $office,
            lat: (float) $data['lat'],
            lng: (float) $data['lng'],
            gpsAccuracyMeters: (float) ($data['gps_accuracy'] ?? 10.0),
            isMockLocation: (bool) ($data['is_mock_location'] ?? false),
            attendanceType: $attendanceType
        );

        $queueAgeHours = $capturedAt->diffInHours(now(), false);
        $isFlagged = $geoResult['is_flagged'];
        $flagReasons = $geoResult['flag_reason'] ? [$geoResult['flag_reason']] : [];

        if ($queueAgeHours > 24 || $capturedAt->isFuture()) {
            $isFlagged = true;
            $flagReasons[] = 'Selisih waktu captured_at sinkronisasi offline melewati batas wajar 24 jam (§5.1)';
        }

        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'date' => $capturedDate,
            'office_location_id' => $office->id,
            'attendance_type' => $attendanceType,
            'status' => 'present',
            'source' => 'offline_sync',
            'clock_in_at' => now(),
            'captured_at' => $capturedAt,
            'lat_in' => (float) $data['lat'],
            'lng_in' => (float) $data['lng'],
            'gps_accuracy_in' => (float) ($data['gps_accuracy'] ?? 10.0),
            'distance_m_in' => $geoResult['distance_m'],
            'is_mock_location' => (bool) ($data['is_mock_location'] ?? false),
            'face_score' => $verifyResult['score'],
            'liveness_score' => $verifyResult['liveness_score'],
            'is_flagged' => $isFlagged,
            'flag_reason' => count($flagReasons) > 0 ? implode('; ', $flagReasons) : null,
            'notes' => $data['notes'] ?? null,
            'client_uuid' => $clientUuid,
        ]);

        AuditLogger::record(
            action: 'attendance.offline_synced',
            auditable: $attendance,
            newValues: [
                'client_uuid' => $clientUuid,
                'captured_at' => $capturedAt->toIso8601String(),
                'is_flagged' => $isFlagged,
            ],
            actorId: $user->id
        );

        return $attendance;
    }

    /**
     * Scheduled Job: Auto-close open attendances without clock-out as `incomplete` (§5.1).
     */
    public function autoCloseIncompleteAttendances(?string $beforeDate = null): int
    {
        $cutoffDate = $beforeDate ?? now('Asia/Jakarta')->toDateString();

        $openAttendances = Attendance::query()
            ->whereDate('date', '<=', $cutoffDate)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->whereNotIn('status', ['incomplete', 'pending_review'])
            ->get();

        foreach ($openAttendances as $att) {
            $att->update([
                'status' => 'incomplete',
                'notes' => trim(($att->notes ? $att->notes.' | ' : '').'Auto-closed oleh sistem karena lupa clock-out (§5.1).'),
            ]);
        }

        return $openAttendances->count();
    }

    public function getConsecutiveFailures(int $userId): int
    {
        return (int) Cache::get($this->failureCacheKey($userId), 0);
    }

    private function calculateLateMinutes(Carbon $officeTime, ?Shift $shift, string $attendanceType): int
    {
        if ($attendanceType === 'remote' || ! $shift || $shift->is_day_off || ! $shift->start_time) {
            return 0;
        }

        $shiftStart = Carbon::parse($officeTime->toDateString().' '.$shift->start_time, 'Asia/Jakarta');
        $toleranceDeadline = $shiftStart->copy()->addMinutes($shift->late_tolerance_minutes);

        if ($officeTime->lessThanOrEqualTo($toleranceDeadline)) {
            return 0;
        }

        return (int) $shiftStart->diffInMinutes($officeTime);
    }

    private function failureCacheKey(int $userId): string
    {
        return "attendance:face_failures:{$userId}";
    }
}
