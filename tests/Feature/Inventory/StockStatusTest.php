<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_quantity_is_out_of_stock_regardless_of_reorder_point(): void
    {
        $product = Product::factory()->create(['reorder_point' => 0]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 0]);

        $this->assertSame('out_of_stock', $inventory->stock_status);
    }

    public function test_quantity_at_or_below_reorder_point_is_low_stock(): void
    {
        $product = Product::factory()->create(['reorder_point' => 20]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 20]);

        $this->assertSame('low_stock', $inventory->stock_status);
    }

    public function test_quantity_above_reorder_point_is_in_stock(): void
    {
        $product = Product::factory()->create(['reorder_point' => 20]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 21]);

        $this->assertSame('in_stock', $inventory->stock_status);
    }

    public function test_a_zero_reorder_point_never_triggers_low_stock(): void
    {
        // Product::reorder_point = 0 means "no threshold configured" (per
        // Product::casts()/DashboardController's documented convention) —
        // any positive quantity should read as in_stock, not low_stock.
        $product = Product::factory()->create(['reorder_point' => 0]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->assertSame('in_stock', $inventory->stock_status);
    }

    public function test_badge_out_of_stock_is_red(): void
    {
        $product = Product::factory()->create(['reorder_point' => 0]);
        $inventory = Inventory::factory()->create(['product_id' => $product->id, 'quantity' => 0]);

        [$class, $label] = $inventory->stock_status_badge;

        $this->assertStringContainsString('danger', $class);
        $this->assertSame('Out of Stock', $label);
    }

    public function test_stock_status_does_not_crash_when_product_relation_is_missing(): void
    {
        // Regression check: getStockStatusAttribute() used to dereference
        // $this->product->reorder_point without a null-safe operator.
        $inventory = Inventory::factory()->create(['quantity' => 5]);
        $inventory->setRelation('product', null);

        $this->assertSame('in_stock', $inventory->stock_status);
    }
}
