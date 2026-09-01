<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::orderBy('name', 'asc')->get();
        return view('base_units.index', compact('units'));
    }

    public function create()
    {
        return view('base_units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:base_units,name',
            'code' => 'nullable|string|max:50|unique:base_units,code',
        ]);

        UnitOfMeasure::create($validated);

        return redirect()->route('base-units.index')->with('success', 'Base unit added successfully.');
    }

    public function edit(UnitOfMeasure $base_unit)
    {
        return view('base_units.edit', ['unit' => $base_unit]);
    }

    public function update(Request $request, UnitOfMeasure $base_unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:base_units,name,' . $base_unit->id,
            'code' => 'nullable|string|max:50|unique:base_units,code,' . $base_unit->id,
        ]);

        $base_unit->update($validated);

        return redirect()->route('base-units.index')->with('success', 'Base unit updated successfully.');
    }

    public function destroy(UnitOfMeasure $base_unit)
    {
        try {
            $base_unit->delete();
            return redirect()->route('base-units.index')->with('success', 'Base unit deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('base-units.index')->with('error', 'Cannot delete this unit because it is currently assigned to one or more products or unit conversions.');
            }

            throw $e;
        }
    }
}
