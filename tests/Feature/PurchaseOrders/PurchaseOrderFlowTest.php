<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_purchase_order_requires_the_product_to_be_linked_to_the_supplier(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();
        $warehouse = Location::factory()->warehouse()->create(['company_id' => $company->company_id]);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->company_id]);
        // Deliberately NOT attaching $product to $supplier.

        $response = $this->actingAs($admin)->post('/purchase-orders', [
            'company_id' => $company->company_id,
            'location_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'product_id' => [$product->id],
            'quantity' => [10],
            'unit_cost' => [50],
        ]);

        $response->assertSessionHasErrors('product_id.0');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_creating_a_purchase_order_for_a_linked_product_succeeds(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();
        $warehouse = Location::factory()->warehouse()->create(['company_id' => $company->company_id]);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->company_id]);
        $product->suppliers()->attach($supplier->id);

        $response = $this->actingAs($admin)->post('/purchase-orders', [
            'company_id' => $company->company_id,
            'location_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(5)->toDateString(),
            'product_id' => [$product->id],
            'quantity' => [10],
            'unit_cost' => [50],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'company_id' => $company->company_id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    public function test_receiving_a_purchase_order_increases_warehouse_stock(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();
        $warehouse = Location::factory()->warehouse()->create(['company_id' => $company->company_id]);
        $product = Product::factory()->create(['company_id' => $company->company_id]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'company_id' => $company->company_id,
            'location_id' => $warehouse->id,
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);
        $purchaseOrder->items()->create([
            'product_id' => $product->id,
            'quantity' => 15,
            'unit_cost' => 20,
        ]);

        $response = $this->actingAs($admin)->post("/purchase-orders/{$purchaseOrder->id}/receive");

        $response->assertRedirect();
        $this->assertSame(PurchaseOrder::STATUS_RECEIVED, $purchaseOrder->fresh()->status);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'location_id' => $warehouse->id,
            'quantity' => 15,
        ]);
    }

    public function test_receiving_into_a_non_warehouse_location_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();
        $store = Location::factory()->asStore()->create(['company_id' => $company->company_id]);
        $product = Product::factory()->create(['company_id' => $company->company_id]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'company_id' => $company->company_id,
            'location_id' => $store->id,
            'status' => PurchaseOrder::STATUS_PENDING,
        ]);
        $purchaseOrder->items()->create([
            'product_id' => $product->id,
            'quantity' => 15,
            'unit_cost' => 20,
        ]);

        $this->actingAs($admin)->post("/purchase-orders/{$purchaseOrder->id}/receive");

        $this->assertSame(PurchaseOrder::STATUS_PENDING, $purchaseOrder->fresh()->status);
        $this->assertDatabaseMissing('inventories', ['product_id' => $product->id]);
    }

    public function test_a_received_order_cannot_be_received_again(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $company = Company::factory()->create();
        $warehouse = Location::factory()->warehouse()->create(['company_id' => $company->company_id]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'company_id' => $company->company_id,
            'location_id' => $warehouse->id,
            'status' => PurchaseOrder::STATUS_RECEIVED,
        ]);

        $response = $this->actingAs($admin)->post("/purchase-orders/{$purchaseOrder->id}/receive");

        $response->assertSessionHas('error');
    }

    public function test_pending_purchase_order_can_be_cancelled(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $purchaseOrder = PurchaseOrder::factory()->create(['status' => PurchaseOrder::STATUS_PENDING]);

        $this->actingAs($admin)->post("/purchase-orders/{$purchaseOrder->id}/cancel");

        $this->assertSame(PurchaseOrder::STATUS_CANCELLED, $purchaseOrder->fresh()->status);
    }
}
