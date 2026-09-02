<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';
    protected $primaryKey = 'company_id'; // Custom primary key for MSSQL

    // Ensures Eloquent treats non-standard keys as auto-incrementing integers
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['company_id', 'name'];

    /**
     * Accessor to handle MSSQL column casing differences (company_id vs COMPANY_ID).
     */
    public function getCompanyIdAttribute()
    {
        return $this->attributes['company_id'] 
            ?? $this->attributes['COMPANY_ID'] 
            ?? $this->attributes['Company_ID'] 
            ?? null;
    }

    /**
     * Get locations owned by this company.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'company_id', 'company_id');
    }

    /**
     * Get products owned by this company.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'company_id', 'company_id');
    }

    /**
     * Get categories owned by this company.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'company_id', 'company_id');
    }

    /**
     * Users granted access to this company (see User::companies()).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id', 'company_id', 'id');
    }
}