<?php

namespace App\Http\Controllers;

use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitConversionController extends Controller
{
    public function index()
    {
        $conversions = UnitConversion::with(['fromUnit', 'toUnit'])
            ->join('base_units', 'base_units.id', '=', 'unit_conversions.from_unit_id')
            ->orderBy('base_units.name')
            ->select('unit_conversions.*')
            ->get();

        return view('unit_conversions.index', compact('conversions'));
    }

    public function create()
    {
        $units = UnitOfMeasure::orderBy('name')->get();

        return view('unit_conversions.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_unit_id' => [
                'required',
                'exists:base_units,id',
                'different:to_unit_id',
            ],
            'to_unit_id' => 'required|exists:base_units,id',
            'factor' => 'required|numeric|gt:0',
        ], [
            'from_unit_id.different' => 'The two units must be different.',
        ]);

        UnitConversion::create($validated);

        return redirect()->route('unit-conversions.index')->with('success', 'Unit conversion added successfully.');
    }

    public function edit(UnitConversion $unit_conversion)
    {
        $units = UnitOfMeasure::orderBy('name')->get();

        $calculatorConversions = UnitConversion::all(['from_unit_id', 'to_unit_id', 'factor'])
            ->map(fn ($c) => [
                'from' => (int) $c->from_unit_id,
                'to' => (int) $c->to_unit_id,
                'factor' => (float) $c->factor,
            ])
            ->values();

        return view('unit_conversions.edit', [
            'conversion' => $unit_conversion,
            'units' => $units,
            'calculatorConversions' => $calculatorConversions,
        ]);
    }

    public function update(Request $request, UnitConversion $unit_conversion)
    {
        $validated = $request->validate([
            'from_unit_id' => [
                'required',
                'exists:base_units,id',
                'different:to_unit_id',
            ],
            'to_unit_id' => 'required|exists:base_units,id',
            'factor' => 'required|numeric|gt:0',
        ], [
            'from_unit_id.different' => 'The two units must be different.',
        ]);

        $unit_conversion->update($validated);

        return redirect()->route('unit-conversions.index')->with('success', 'Unit conversion updated successfully.');
    }

    public function destroy(UnitConversion $unit_conversion)
    {
        // Without this check, deleting a conversion would cascade-delete
        // the product_unit_conversions pivot rows silently — an active
        // product that explicitly selected this conversion would lose it
        // with no warning.
        $isUsedByActiveProduct = $unit_conversion->products()->where('is_active', true)->exists();

        if ($isUsedByActiveProduct) {
            return redirect()->route('unit-conversions.index')
                ->with('error', 'Cannot delete this conversion because it is currently in use by active products.');
        }

        $unit_conversion->delete();

        return redirect()->route('unit-conversions.index')->with('success', 'Unit conversion deleted successfully.');
    }
}
