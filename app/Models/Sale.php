<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Read-only view onto the `sales` table written by the companion POS
 * application (shares this app's database). Used only for reporting here —
 * sales themselves are created/refunded exclusively from the POS app.
 */
class Sale extends Model
{
    protected $table = 'sales';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
