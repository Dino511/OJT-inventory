<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        $supplier = Supplier::factory()->create();

        return [
            'company_id' => Company::factory(),
            'supplier_id' => $supplier->id,
            'location_id' => Location::factory()->warehouse(),
            'supplier_name' => $supplier->name,
            'status' => PurchaseOrder::STATUS_PENDING,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'notes' => null,
            'created_by' => null,
            'received_by' => null,
            'received_at' => null,
        ];
    }
}
