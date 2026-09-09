<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
    ];

    /**
     * Get the product associated with the inventory record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the location associated with the inventory record.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * 'out_of_stock' | 'low_stock' | 'in_stock', derived live from the
     * current quantity and the product's reorder point — same threshold the
     * Dashboard's "Needs Attention" widget uses — so it always reflects the
     * latest quantity with no separate column to keep in sync.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }

        $reorderPoint = $this->product?->reorder_point ?? 0;

        if ($reorderPoint > 0 && $this->quantity <= $reorderPoint) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * [badge CSS classes, label] for the current stock_status — kept next to
     * the accessor above so the two can't drift out of sync.
     */
    public function getStockStatusBadgeAttribute(): array
    {
        return match ($this->stock_status) {
            'out_of_stock' => ['bg-danger-subtle text-danger', 'Out of Stock'],
            'low_stock' => ['bg-warning-subtle text-warning', 'Low Stock'],
            default => ['bg-success-subtle text-success', 'In Stock'],
        };
    }
}