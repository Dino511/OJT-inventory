<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends Model
{
    use HasFactory;

    protected $table = 'unit_conversions';

    protected $fillable = [
        'from_unit_id',
        'to_unit_id',
        'factor',
    ];

    protected $casts = [
        'factor' => 'decimal:4',
    ];

    /**
     * The larger/source unit, e.g. "Box" in "1 Box = 12 Pieces".
     */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'from_unit_id');
    }

    /**
     * The smaller/target unit, e.g. "Piece" in "1 Box = 12 Pieces".
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'to_unit_id');
    }

    /**
     * Convert a quantity from one base unit to another using the defined
     * conversion rules, checking both the direct rule and its inverse.
     * Returns null when no conversion path between the two units exists.
     */
    public static function convert(int $fromUnitId, int $toUnitId, float $quantity): ?float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $direct = static::where('from_unit_id', $fromUnitId)->where('to_unit_id', $toUnitId)->first();
        if ($direct) {
            return $quantity * (float) $direct->factor;
        }

        $inverse = static::where('from_unit_id', $toUnitId)->where('to_unit_id', $fromUnitId)->first();
        if ($inverse && (float) $inverse->factor != 0) {
            return $quantity / (float) $inverse->factor;
        }

        return null;
    }
}
