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
     * Default system setting definitions.
     *
     * @return array<int, array<string, string>>
     */
    public static function getDefaultDefinitions(): array
    {
        return [
            // Attendance
            [
                'key' => 'attendance.late_fee_per_minute',
                'group' => 'attendance',
                'label' => 'Tarif Denda Keterlambatan per Menit (Rp)',
                'value' => '250',
                'type' => 'decimal',
                'description' => 'Tarif potongan keterlambatan per menit (Rp 15.000/jam = Rp 250/menit).',
            ],
            [
                'key' => 'attendance.late_tolerance_minutes',
                'group' => 'attendance',
                'label' => 'Toleransi Keterlambatan Default (Menit)',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Batas menit toleransi sebelum denda keterlambatan mulai dihitung.',
            ],
            [
                'key' => 'attendance.daily_max_late_fee',
                'group' => 'attendance',
                'label' => 'Batas Maksimum Denda Terlambat Harian (Rp)',
                'value' => '50000',
                'type' => 'decimal',
                'description' => 'Plafon maksimum potongan denda keterlambatan per hari kerja.',
            ],
            [
                'key' => 'attendance.default_alpa_fee',
                'group' => 'attendance',
                'label' => 'Tarif Potongan Alpa Default per Hari (Rp)',
                'value' => '25000',
                'type' => 'decimal',
                'description' => 'Potongan alpa per hari kerja jika profil karyawan tidak memiliki tarif khusus.',
            ],
            [
                'key' => 'attendance.correction_window_days',
                'group' => 'attendance',
                'label' => 'Batas Waktu Pengajuan Koreksi Absensi (Hari)',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Maksimal jumlah hari kalender sejak tanggal absensi untuk mengajukan koreksi.',
            ],
            // Biometric
            [
                'key' => 'face.verify_threshold',
                'group' => 'biometric',
                'label' => 'Ambang Batas Skor Verifikasi Wajah 1:1',
                'value' => '0.50',
                'type' => 'float',
                'description' => 'Ambang batas kemiripan (cosine similarity) InsightFace untuk validasi wajah karyawan.',
            ],
            [
                'key' => 'face.max_failed_attempts',
                'group' => 'biometric',
                'label' => 'Maksimum Gagal Wajah Sebelum Jalur Manual',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Setelah 3 kali gagal verifikasi wajah beruntun, absensi diarahkan ke tinjauan manual HRD.',
            ],
            // Geofence
            [
                'key' => 'geofence.default_radius_m',
                'group' => 'geofence',
                'label' => 'Radius Geofence Kantor Default (Meter)',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Radius toleransi jarak lokasi presensi dari titik koordinat kantor.',
            ],
            [
                'key' => 'geofence.max_gps_accuracy_m',
                'group' => 'geofence',
                'label' => 'Batas Akurasi GPS Maksimum Tanpa Flag (Meter)',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Absensi dengan akurasi GPS di atas nilai ini otomatis ditandai mencurigakan.',
            ],
            // Leave / Cuti
            [
                'key' => 'leave.annual_quota_days',
                'group' => 'leave',
                'label' => 'Kuota Cuti Tahunan (Hari Kerja)',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Total kuota hak cuti tahunan reguler bagi seluruh karyawan.',
            ],
            [
                'key' => 'leave.monthly_max_days',
                'group' => 'leave',
                'label' => 'Batas Maksimum Cuti Bulanan (Hari Kerja)',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Batas maksimal pengambilan jatah cuti dalam satu periode bulan.',
            ],
            [
                'key' => 'leave.alpa_converts_to_leave',
                'group' => 'leave',
                'label' => 'Konversi Otomatis Alpa ke Kuota Cuti',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Opsi konversi otomatis alpa menjadi pemotongan cuti tahunan dengan masa sanggah.',
            ],
            [
                'key' => 'leave.division_max_concurrent',
                'group' => 'leave',
                'label' => 'Batas Maksimum Cuti Bersamaan per Divisi',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Maksimal jumlah karyawan di divisi yang sama yang boleh cuti pada hari yang sama.',
            ],
            [
                'key' => 'leave.sick_attachment_min_days',
                'group' => 'leave',
                'label' => 'Batas Hari Cuti Sakit Wajib Surat Dokter',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Pengajuan cuti sakit lebih dari jumlah hari ini wajib mengunggah surat keterangan dokter.',
            ],
            // Ticketing & SLA
            [
                'key' => 'ticket.sla_backbone_minutes',
                'group' => 'ticketing',
                'label' => 'Target SLA Tiket Backbone (Menit)',
                'value' => '360',
                'type' => 'integer',
                'description' => 'Target durasi penyelesaian penanganan insiden jaringan Backbone BDG- (360 menit / 6 jam).',
            ],
            [
                'key' => 'ticket.sla_broadband_minutes',
                'group' => 'ticketing',
                'label' => 'Target SLA Tiket Broadband (Menit)',
                'value' => '240',
                'type' => 'integer',
                'description' => 'Target durasi penanganan insiden pelanggan Broadband BRD- (240 menit / 4 jam).',
            ],
            [
                'key' => 'ticket.chronology_warning_minutes',
                'group' => 'ticketing',
                'label' => 'Batas Waktu Kronologis OVERDUE (Menit)',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Batas maksimal jeda waktu pembaruan status kronologis tiket yang sedang berjalan.',
            ],
            [
                'key' => 'ticket.stop_clock_approval_threshold_minutes',
                'group' => 'ticketing',
                'label' => 'Batas Stop Clock Wajib Approval (Menit)',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Jeda hitung SLA (Stop Clock) di atas durasi ini wajib persetujuan Manager Teknis.',
            ],
            // GoMSN & Financial
            [
                'key' => 'gomsn.executive_approval_threshold',
                'group' => 'gomsn',
                'label' => 'Ambang Nominal Wajib Approval Eksekutif (Rp)',
                'value' => '5000000',
                'type' => 'decimal',
                'description' => 'Pengajuan operasional melebihi nominal ini wajib verifikasi tambahan Direksi/CEO.',
            ],
            // Security
            [
                'key' => 'security.max_devices_per_user',
                'group' => 'security',
                'label' => 'Maksimal Perangkat Terdaftar per Karyawan',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Maksimal jumlah perangkat aktif (device binding) yang diizinkan untuk satu akun.',
            ],
            [
                'key' => 'security.session_idle_timeout_minutes',
                'group' => 'security',
                'label' => 'Batas Waktu Idle Sesi Login (Menit)',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Sesi login akan otomatis berakhir bila pengguna tidak aktif melebihi batas waktu ini.',
            ],
        ];
    }

    /**
     * Ensure default settings exist in database.
     */
    public static function ensureDefaults(?int $userId = null): int
    {
        $definitions = self::getDefaultDefinitions();
        $inserted = 0;
        $now = now()->startOfMonth();

        foreach ($definitions as $item) {
            $exists = SystemSetting::query()->where('key', $item['key'])->exists();
            if (! $exists) {
                SystemSetting::query()->create(array_merge($item, [
                    'effective_from' => $now,
                    'updated_by' => $userId ?? Auth::id(),
                    'change_reason' => 'Inisialisasi parameter default sistem',
                ]));
                $inserted++;
            }
        }

        return $inserted;
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
