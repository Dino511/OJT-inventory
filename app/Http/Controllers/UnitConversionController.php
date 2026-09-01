<?php

namespace App\Http\Controllers;

use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                Rule::unique('unit_conversions', 'from_unit_id')->where(fn ($query) => $query->where('to_unit_id', $request->to_unit_id)),
            ],
            'to_unit_id' => 'required|exists:base_units,id',
            'factor' => 'required|numeric|gt:0',
        ], [
            'from_unit_id.different' => 'The two units must be different.',
            'from_unit_id.unique' => 'A conversion between these two units already exists. Edit that one instead.',
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
                Rule::unique('unit_conversions', 'from_unit_id')
                    ->where(fn ($query) => $query->where('to_unit_id', $request->to_unit_id))
                    ->ignore($unit_conversion->id),
            ],
            'to_unit_id' => 'required|exists:base_units,id',
            'factor' => 'required|numeric|gt:0',
        ], [
            'from_unit_id.different' => 'The two units must be different.',
            'from_unit_id.unique' => 'A conversion between these two units already exists. Edit that one instead.',
        ]);

        $unit_conversion->update($validated);

        return redirect()->route('unit-conversions.index')->with('success', 'Unit conversion updated successfully.');
    }

    public function destroy(UnitConversion $unit_conversion)
    {
        $unit_conversion->delete();

        return redirect()->route('unit-conversions.index')->with('success', 'Unit conversion deleted successfully.');
    }
}
