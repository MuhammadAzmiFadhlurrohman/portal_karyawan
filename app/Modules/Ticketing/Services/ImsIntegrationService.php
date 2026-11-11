<?php

namespace App\Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class ImsIntegrationService
{
    /**
     * Search IMS customers by name, phone, or CID (§7.1).
     * If IMS is unavailable, returns empty array with available=false flag (AC-11).
     *
     * @return array{available: bool, customers: array<int, array<string, mixed>>}
     */
    public function searchCustomers(string $query): array
    {
        try {
            $customers = DB::connection('ims_v3')
                ->table('customers')
                ->where('cid', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->limit(20)
                ->get()
                ->map(fn ($c) => (array) $c)
                ->toArray();

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
     * Get specific customer detail snapshot by CID (§7.1).
     *
     * @return array{available: bool, customer: ?array<string, mixed>}
     */
    public function getCustomerByCid(string $cid): array
    {
        try {
            $customer = DB::connection('ims_v3')
                ->table('customers')
                ->where('cid', $cid)
                ->first();

            return [
                'available' => true,
                'customer' => $customer ? (array) $customer : null,
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
