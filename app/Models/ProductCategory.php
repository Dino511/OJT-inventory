<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
    ];

    /**
     * Get the company that owns the category.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The main category this row is a subcategory of, if any.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Every subcategory nested directly under this category. Only one
     * level of nesting is supported — a subcategory's own children()
     * is never populated/used.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * "Beverage" for a top-level category, "Beverage > Soda" for a
     * subcategory — used anywhere a category needs to be identified
     * unambiguously outside of a nested list (e.g. the Products table).
     */
    public function getFullNameAttribute(): string
    {
        return $this->parent ? "{$this->parent->name} > {$this->name}" : $this->name;
    }
}
