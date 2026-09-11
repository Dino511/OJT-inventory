<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'secondary_contact_person',
        'phone',
        'secondary_phone',
        'email',
        'address',
    ];

    /**
     * Every product this supplier can provide. A product may be supplied
     * by more than one supplier (and vice versa), hence the pivot rather
     * than a plain supplier_id column on products.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier');
    }
}
