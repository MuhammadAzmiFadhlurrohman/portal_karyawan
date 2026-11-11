<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * Issue a 6-digit OTP stored as hash, valid for 5 minutes, max 3 attempts (§6.1).
     *
     * @param  array<string, mixed>  $metadata
     * @return array{otp: OtpCode, plain_code: string}
     */
    public function issue(User $user, string $purpose, array $metadata = []): array
    {
        // Invalidate previous unused OTPs for the same user and purpose
        OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpCode::query()->create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code_hash' => Hash::make($plainCode),
            'attempts' => 0,
            'max_attempts' => 3,
            'expires_at' => now()->addMinutes(5),
            'metadata' => $metadata,
        ]);

        AuditLogger::record(
            action: "otp.issued.{$purpose}",
            auditable: $otp,
            newValues: ['purpose' => $purpose, 'expires_at' => $otp->expires_at->toIso8601String()],
            actorId: $user->id
        );

        return [
            'otp' => $otp,
            'plain_code' => $plainCode,
        ];
    }

    /**
     * Verify a 6-digit OTP code (§6.1).
     */
    public function verify(User $user, string $purpose, string $code): bool
    {
        $otp = OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->attempts >= $otp->max_attempts) {
            return false;
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            AuditLogger::record(
                action: "otp.failed.{$purpose}",
                auditable: $otp,
                newValues: ['attempts' => $otp->attempts],
                actorId: $user->id
            );

            return false;
        }

        $otp->update(['used_at' => now()]);

        AuditLogger::record(
            action: "otp.verified.{$purpose}",
            auditable: $otp,
            actorId: $user->id
        );

        return true;
    }
}
