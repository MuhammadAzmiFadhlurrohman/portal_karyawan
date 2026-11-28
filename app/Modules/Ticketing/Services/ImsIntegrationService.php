<?php

namespace App\Modules\Ticketing\Services;

use App\Models\OdpCustomer;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImsIntegrationService
{
    /**
     * Search IMS customers by nomor_internet, nama_pelanggan, or nomor_hp from view_batchjob.
     * Enriched with ODP and OLT from GOMSN FTTH (odp_customers -> odp -> pon -> olt).
     *
     * @return array{available: bool, customers: array<int, array<string, mixed>>}
     */
    public function searchCustomers(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'available' => true,
                'customers' => [],
            ];
        }

        try {
            $connectionName = config('database.connections.ims') ? 'ims' : 'ims_v3';

            $rows = DB::connection($connectionName)
                ->table('view_batchjob')
                ->where(function ($q) use ($query) {
                    $q->where('nomor_internet', 'like', "%{$query}%")
                        ->orWhere('nama_pelanggan', 'like', "%{$query}%")
                        ->orWhere('nomor_hp', 'like', "%{$query}%");
                })
                ->limit(25)
                ->get([
                    'nomor_internet',
                    'nama_pelanggan',
                    'nomor_hp',
                    'alamat_pasang',
                    'nama_kategori_bandwith',
                    'desc_registrasi',
                    'nama_pop',
                ]);

            if ($rows->isEmpty()) {
                return [
                    'available' => true,
                    'customers' => [],
                ];
            }

            // Cross-reference with GOMSN OLT/PON/ODP infrastructure (odp_customers)
            $nomorList = $rows->pluck('nomor_internet')->filter()->unique()->toArray();
            $odpMap = [];
            if (!empty($nomorList)) {
                try {
                    $odpCusts = OdpCustomer::with(['odp.pon.olt'])
                        ->whereIn('nomor_internet', $nomorList)
                        ->get();

                    foreach ($odpCusts as $oc) {
                        $odpName = $oc->odp?->nama_odp ?? '';
                        $oltName = $oc->odp?->pon?->olt?->nama_olt ?? $oc->olt_name ?? '';
                        $odpMap[$oc->nomor_internet] = [
                            'odp' => $odpName,
                            'olt' => $oltName,
                        ];
                    }
                } catch (Throwable $e) {
                    // Fail silently on local ODP join if schema differences
                }
            }

            $customers = $rows->map(function ($r) use ($odpMap) {
                $nomorInternet = (string) ($r->nomor_internet ?? '');
                $odpInfo = $odpMap[$nomorInternet] ?? null;
                $oltVal = $odpInfo['olt'] ?? ($r->nama_pop ?? '');

                return [
                    'cid' => $nomorInternet,
                    'nomor_internet' => $nomorInternet,
                    'id_pelanggan' => $nomorInternet,
                    'name' => (string) ($r->nama_pelanggan ?? ''),
                    'nama' => (string) ($r->nama_pelanggan ?? ''),
                    'phone' => (string) ($r->nomor_hp ?? ''),
                    'telepon' => (string) ($r->nomor_hp ?? ''),
                    'nomor_hp' => (string) ($r->nomor_hp ?? ''),
                    'address' => (string) ($r->alamat_pasang ?? ''),
                    'alamat' => (string) ($r->alamat_pasang ?? ''),
                    'alamat_pasang' => (string) ($r->alamat_pasang ?? ''),
                    'odp' => $odpInfo['odp'] ?? '',
                    'olt' => $oltVal,
                    'pop' => $oltVal,
                    'layanan' => (string) ($r->nama_kategori_bandwith ?? ''),
                    'bandwidth' => (string) ($r->nama_kategori_bandwith ?? ''),
                    'status' => (string) ($r->desc_registrasi ?? ''),
                ];
            })->values()->toArray();

            return [
                'available' => true,
                'customers' => $customers,
            ];
        } catch (Throwable $e) {
            // Graceful fallback when IMS database/service is down (AC-11)
            return [
                'available' => false,
                'customers' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get specific customer detail snapshot by nomor_internet / CID from view_batchjob.
     *
     * @return array{available: bool, customer: ?array<string, mixed>}
     */
    public function getCustomerByCid(string $cid): array
    {
        try {
            $connectionName = config('database.connections.ims') ? 'ims' : 'ims_v3';
            $r = DB::connection($connectionName)
                ->table('view_batchjob')
                ->where('nomor_internet', $cid)
                ->first();

            if (! $r) {
                return [
                    'available' => true,
                    'customer' => null,
                ];
            }

            $odpInfo = null;
            try {
                $oc = OdpCustomer::with(['odp.pon.olt'])
                    ->where('nomor_internet', $r->nomor_internet)
                    ->first();
                if ($oc) {
                    $odpInfo = [
                        'odp' => $oc->odp?->nama_odp ?? '',
                        'olt' => $oc->odp?->pon?->olt?->nama_olt ?? $oc->olt_name ?? '',
                    ];
                }
            } catch (Throwable $e) {
            }

            $oltVal = $odpInfo['olt'] ?? ($r->nama_pop ?? '');

            return [
                'available' => true,
                'customer' => [
                    'cid' => (string) ($r->nomor_internet ?? ''),
                    'nomor_internet' => (string) ($r->nomor_internet ?? ''),
                    'id_pelanggan' => (string) ($r->nomor_internet ?? ''),
                    'name' => (string) ($r->nama_pelanggan ?? ''),
                    'nama' => (string) ($r->nama_pelanggan ?? ''),
                    'phone' => (string) ($r->nomor_hp ?? ''),
                    'telepon' => (string) ($r->nomor_hp ?? ''),
                    'nomor_hp' => (string) ($r->nomor_hp ?? ''),
                    'address' => (string) ($r->alamat_pasang ?? ''),
                    'alamat' => (string) ($r->alamat_pasang ?? ''),
                    'alamat_pasang' => (string) ($r->alamat_pasang ?? ''),
                    'odp' => $odpInfo['odp'] ?? '',
                    'olt' => $oltVal,
                    'pop' => $oltVal,
                    'layanan' => (string) ($r->nama_kategori_bandwith ?? ''),
                    'bandwidth' => (string) ($r->nama_kategori_bandwith ?? ''),
                    'status' => (string) ($r->desc_registrasi ?? ''),
                ],
            ];
        } catch (Throwable $e) {
            return [
                'available' => false,
                'customer' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}

