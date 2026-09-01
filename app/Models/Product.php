<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'description', 
        'sku',
        'code',
        'unit_value',
        'base_unit_id',
        'reorder_point',
        'selling_price',
        'cost',
    ];

    public function company()
{
    return $this->belongsTo(Company::class, 'company_id', 'company_id'); 
    // Note: Adjust 'company_id' if your primary key on the companies table is just 'id'
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
}