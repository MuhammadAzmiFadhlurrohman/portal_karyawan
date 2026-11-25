<?php

namespace App\Http\Controllers;

use App\Models\Odp;
use App\Models\Olt;
use App\Models\Pon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoverageController extends Controller
{
    /**
     * Tampilkan Halaman Cek Coverage Lokasi ke ODP Terdekat (Presisi FTTH).
     */
    public function coverage(Request $request): View|JsonResponse
    {
        // Ambil seluruh data ODP beserta relasi PON dan OLT
        $odpRecords = Odp::query()
            ->with(['pon.olt'])
            ->orderBy('nama_odp')
            ->get();

        $cleanOdps = [];
        $totalPorts = 0;
        $totalUsedPorts = 0;

        foreach ($odpRecords as $odp) {
            $coords = self::sanitizeCoordinate($odp->latitude, $odp->longitude);
            if (! $coords) {
                continue;
            }

            [$lat, $lng] = $coords;
            $maxPort = max(1, (int) ($odp->port_max ?: 8));
            $usedPort = max(0, (int) $odp->port_terpakai);
            $availablePort = max(0, $maxPort - $usedPort);
            $hasSlot = $availablePort > 0;

            $totalPorts += $maxPort;
            $totalUsedPorts += min($usedPort, $maxPort);

            $cleanOdps[] = [
                'id' => $odp->id,
                'nama_odp' => trim($odp->nama_odp),
                'lat' => $lat,
                'lng' => $lng,
                'port_max' => $maxPort,
                'port_terpakai' => $usedPort,
                'port_tersedia' => $availablePort,
                'status_port' => $hasSlot ? 'available' : 'full',
                'has_slot' => $hasSlot,
                'nama_pon' => $odp->pon?->nama_pon ?? 'PON Umum',
                'nama_olt' => $odp->pon?->olt?->nama_olt ?? 'OLT MSN',
                'kode_olt' => $odp->pon?->olt?->kode ?? 'MSN',
                'lokasi_olt' => $odp->pon?->olt?->lokasi ?? 'Bandung',
                'keterangan' => $odp->keterangan ?: 'ODP Pole FTTH',
                'url_detail' => ($odp->pon && $odp->pon->olt)
                    ? route('gomsn.network.odps.show', [$odp->pon->olt_id, $odp->pon_id, $odp->id])
                    : null,
            ];
        }

        // Hitung centroid default (rata-rata koordinat atau default Bandung Pusat)
        $defaultLat = -6.917464;
        $defaultLng = 107.619123;
        if (! empty($cleanOdps)) {
            $avgLat = array_sum(array_column($cleanOdps, 'lat')) / count($cleanOdps);
            $avgLng = array_sum(array_column($cleanOdps, 'lng')) / count($cleanOdps);
            if ($avgLat >= -11.5 && $avgLat <= 6.5 && $avgLng >= 95.0 && $avgLng <= 142.0) {
                $defaultLat = round($avgLat, 6);
                $defaultLng = round($avgLng, 6);
            }
        }

        // Cek parameter input langsung dari request (query string lat/lng atau link maps)
        $initialTarget = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $parsed = self::sanitizeCoordinate($request->input('lat'), $request->input('lng'));
            if ($parsed) {
                $initialTarget = [
                    'lat' => $parsed[0],
                    'lng' => $parsed[1],
                    'label' => $request->input('label', 'Titik Target Pengguna'),
                ];
            }
        } elseif ($request->filled('q')) {
            $parsed = self::parseLocationQuery($request->input('q'));
            if ($parsed) {
                $initialTarget = [
                    'lat' => $parsed[0],
                    'lng' => $parsed[1],
                    'label' => $request->input('label', 'Hasil Pencarian: ' . substr($request->input('q'), 0, 30)),
                ];
            }
        }

        $metrics = [
            'total_odp' => count($cleanOdps),
            'total_ports' => $totalPorts,
            'total_used_ports' => $totalUsedPorts,
            'total_available_ports' => max(0, $totalPorts - $totalUsedPorts),
            'utilization_rate' => $totalPorts > 0 ? round(($totalUsedPorts / $totalPorts) * 100, 1) : 0,
            'default_center' => [
                'lat' => $defaultLat,
                'lng' => $defaultLng,
            ],
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'initial_target' => $initialTarget,
                'odps' => $cleanOdps,
            ]);
        }

        return view('gomsn.network.coverage', [
            'odps' => $cleanOdps,
            'metrics' => $metrics,
            'initialTarget' => $initialTarget,
        ]);
    }

    /**
     * Sanitasi dan koreksi koordinat Latitude & Longitude otomatis:
     * - Memperbaiki typo tidak ada titik desimal (-702417 -> -7.02417, 10754093 -> 107.54093).
     * - Menukar lat dan lng jika posisi terbalik (misal lat > 50 dan lng < 0 di Indonesia).
     * - Memperbaiki tanda minus jika user lupa tanda negatif pada latitude Indonesia (-6.x s/d -8.x).
     * - Memfilter koordinat sampah di luar batas jangkauan Indonesia.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function sanitizeCoordinate($rawLat, $rawLng): ?array
    {
        if ($rawLat === null || $rawLng === null) {
            return null;
        }

        $latStr = trim(str_replace(',', '.', (string) $rawLat));
        $lngStr = trim(str_replace(',', '.', (string) $rawLng));

        // Hapus karakter non-numerik selain minus dan titik
        $latStr = preg_replace('/[^0-9\.\-]/', '', $latStr);
        $lngStr = preg_replace('/[^0-9\.\-]/', '', $lngStr);

        if ($latStr === '' || $lngStr === '') {
            return null;
        }

        // 1. Perbaikan typo: titik desimal hilang (contoh: -702417 -> -7.02417)
        if (preg_match('/^(-?[1-9])([0-9]{4,})$/', $latStr, $m)) {
            $latStr = $m[1] . '.' . $m[2];
        }
        // Contoh: 10754093 -> 107.54093
        if (preg_match('/^(1[0-4][0-9])([0-9]{4,})$/', $lngStr, $m)) {
            $lngStr = $m[1] . '.' . $m[2];
        }

        $lat = (float) $latStr;
        $lng = (float) $lngStr;

        // Jika bernilai 0,0 atau identik (kemungkinan placeholder error)
        if (abs($lat) < 0.0001 && abs($lng) < 0.0001) {
            return null;
        }

        // 2. Deteksi Koordinat Terbalik (Latitude diisi Longitude & sebaliknya)
        // Di Indonesia: Longitude selalu ~95.0 s/d ~141.5, Latitude selalu -11.0 s/d 6.0
        if ($lat >= 95.0 && $lat <= 141.5 && $lng >= -11.5 && $lng <= 6.5) {
            $tmp = $lat;
            $lat = $lng;
            $lng = $tmp;
        }

        // 3. Deteksi kelupaan minus pada Latitude di Pulau Jawa/Bandung (-6.0 s/d -8.5)
        if ($lat >= 5.5 && $lat <= 8.5 && $lng >= 105.0 && $lng <= 112.0) {
            $lat = -$lat;
        }

        // 4. Cek duplikasi koordinat konyol (misal lat = lng)
        if (abs($lat - $lng) < 0.5) {
            return null;
        }

        // 5. Validasi batas wilayah operasional Indonesia
        if ($lat < -11.5 || $lat > 6.5 || $lng < 95.0 || $lng > 142.0) {
            return null;
        }

        return [round($lat, 7), round($lng, 7)];
    }

    /**
     * Parsing input pencarian pintar:
     * - Pasangan "lat, lng" atau "lat lng"
     * - Link Google Maps (https://maps.google.com/..., @lat,lng, q=lat,lng)
     *
     * @return array{0: float, 1: float}|null
     */
    public static function parseLocationQuery(string $input): ?array
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        // 1. Ekstrak dari URL Google Maps format @lat,lng (misal: /@ -6.936988,107.5904512,17z)
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $input, $m)) {
            return self::sanitizeCoordinate($m[1], $m[2]);
        }

        // 2. Ekstrak dari URL Google Maps format q=lat,lng atau ll=lat,lng
        if (preg_match('/[?&](?:q|ll|query|destination|daddr)=(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)/', $input, $m)) {
            return self::sanitizeCoordinate($m[1], $m[2]);
        }

        // 3. Ekstrak dari teks biasa "lat, lng" atau "lat lng"
        if (preg_match('/^(-?\d+[\.,]?\d*)[\s,;]+(-?\d+[\.,]?\d*)$/', $input, $m)) {
            return self::sanitizeCoordinate($m[1], $m[2]);
        }

        return null;
    }

    /**
     * API Helper untuk mengambil rute jalan turn-by-turn dari OSRM.
     * Digunakan agar garis tarikan kabel di peta mengikuti kontur jalan raya, bukan garis lurus.
     */
    public function getRoute(Request $request): JsonResponse
    {
        $origin = $request->input('origin'); // "lat,lng"
        $dest = $request->input('dest');     // "lat,lng"

        if (! $origin || ! $dest) {
            return response()->json(['success' => false, 'message' => 'Origin dan destination wajib diisi.'], 400);
        }

        $origCoords = explode(',', $origin);
        $destCoords = explode(',', $dest);

        if (count($origCoords) !== 2 || count($destCoords) !== 2) {
            return response()->json(['success' => false, 'message' => 'Format koordinat tidak valid.'], 400);
        }

        $lat1 = (float) trim($origCoords[0]);
        $lng1 = (float) trim($origCoords[1]);
        $lat2 = (float) trim($destCoords[0]);
        $lng2 = (float) trim($destCoords[1]);

        // OSRM menggunakan urutan lng,lat
        $url = sprintf(
            'https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F?overview=full&geometries=geojson',
            $lng1, $lat1, $lng2, $lat2
        );

        $cacheKey = 'ftth_route_' . md5($url);
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) GoMSN-FTTH/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $data = json_decode($res, true);
                if (($data['code'] ?? '') === 'Ok' && ! empty($data['routes'][0]['geometry']['coordinates'])) {
                    // Konversi koordinat OSRM [lng, lat] ke Leaflet [lat, lng]
                    $leafletCoords = array_map(function ($pt) {
                        return [(float) $pt[1], (float) $pt[0]];
                    }, $data['routes'][0]['geometry']['coordinates']);

                    $result = [
                        'success' => true,
                        'distance' => (float) $data['routes'][0]['distance'],
                        'duration' => (float) ($data['routes'][0]['duration'] ?? 0),
                        'geometry' => $leafletCoords,
                    ];

                    cache()->put($cacheKey, $result, now()->addHours(6));

                    return response()->json($result);
                }
            }
        } catch (\Throwable $e) {
            // Fallback gracefully below
        }

        return response()->json(['success' => false, 'message' => 'Gagal mengambil rute jalan dari OSRM.'], 500);
    }
}
