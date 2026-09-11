<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'description',
        'code',
        'unit_value',
        'base_unit_id',
        'unit_conversion_id',
        'reorder_point',
        'selling_price',
        'cost',
        'photo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit_value' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'reorder_point' => 'decimal:2',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the base unit associated with the product.
     */
    public function baseUnit()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'base_unit_id');
    }

    /**
     * Every stock record for this product, one per location.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id');
    }

    /**
     * Every purchase-order line item that has ever ordered this product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'product_id');
    }

    /**
     * Every stock-transfer line item that has ever moved this product.
     */
    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'product_id');
    }

    /**
     * The single global unit conversion (from this product's base unit)
     * that applies to this specific product — since the same base unit can
     * have more than one conversion defined (e.g. two different
     * Box -> Piece factors for different products), each product picks
     * exactly one that's meaningful for it.
     */
    public function unitConversion(): BelongsTo
    {
        return $this->belongsTo(UnitConversion::class, 'unit_conversion_id');
    }

    /**
     * Every supplier that can provide this product. A product may have
     * more than one supplier (and vice versa), hence the pivot rather
     * than a plain supplier_id column here.
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier');
    }
}