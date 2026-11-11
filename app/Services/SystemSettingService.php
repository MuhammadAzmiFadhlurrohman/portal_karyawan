<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    /**
     * Retrieve a system setting value effective as of a given timestamp (§5.5, §6.3, AC-09).
     */
    public static function get(string $key, mixed $default = null, ?CarbonInterface $asOf = null): mixed
    {
        if ($asOf === null) {
            return Cache::remember("system_setting:{$key}:latest", 60, function () use ($key, $default) {
                $setting = SystemSetting::query()
                    ->where('key', $key)
                    ->where('effective_from', '<=', now())
                    ->orderByDesc('effective_from')
                    ->orderByDesc('id')
                    ->first();

                return $setting ? $setting->typedValue() : $default;
            });
        }

        $setting = SystemSetting::query()
            ->where('key', $key)
            ->where('effective_from', '<=', $asOf)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        return $setting ? $setting->typedValue() : $default;
    }

    public static function getInt(string $key, int $default = 0, ?CarbonInterface $asOf = null): int
    {
        return (int) self::get($key, $default, $asOf);
    }

    public static function getFloat(string $key, float $default = 0.0, ?CarbonInterface $asOf = null): float
    {
        return (float) self::get($key, $default, $asOf);
    }

    public static function getBool(string $key, bool $default = false, ?CarbonInterface $asOf = null): bool
    {
        $val = self::get($key, $default, $asOf);
        if (is_bool($val)) {
            return $val;
        }
        if (is_string($val)) {
            return in_array(strtolower($val), ['true', '1', 'yes', 'on'], true);
        }

        return (bool) $val;
    }

    /**
     * Create a new versioned setting entry with audit history (§6.3, AC-09).
     */
    public static function set(
        string $key,
        mixed $value,
        string $reason,
        ?CarbonInterface $effectiveFrom = null,
        ?string $group = null,
        ?string $label = null,
        ?string $type = null,
        ?string $description = null
    ): SystemSetting {
        $latest = SystemSetting::query()
            ->where('key', $key)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        $resolvedValue = is_array($value) ? (string) json_encode($value) : (string) $value;

        $newSetting = SystemSetting::query()->create([
            'key' => $key,
            'group' => $group ?? ($latest ? $latest->group : 'general'),
            'label' => $label ?? ($latest ? $latest->label : $key),
            'value' => $resolvedValue,
            'type' => $type ?? ($latest ? $latest->type : 'string'),
            'description' => $description ?? $latest?->description,
            'effective_from' => $effectiveFrom ?? now(),
            'updated_by' => Auth::id(),
            'change_reason' => $reason,
        ]);

        Cache::forget("system_setting:{$key}:latest");

        AuditLogger::record(
            action: 'system_setting.updated',
            auditable: $newSetting,
            oldValues: $latest ? ['value' => $latest->value, 'effective_from' => $latest->effective_from?->toIso8601String()] : null,
            newValues: ['value' => $newSetting->value, 'effective_from' => $newSetting->effective_from?->toIso8601String()],
            reason: $reason
        );

        return $newSetting;
    }
}
