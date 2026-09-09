<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            'code' => 'required|string|max:50|unique:base_units,code',
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
            'code' => 'required|string|max:50|unique:base_units,code,' . $base_unit->id,
        ]);

        $base_unit->update($validated);

        return redirect()->route('base-units.index')->with('success', 'Base unit updated successfully.');
    }

    public function destroy(UnitOfMeasure $base_unit)
    {
        // Explicit pre-check for active products so the error names the
        // actual reason — the database FK below still catches every other
        // case (inactive products, unit conversions) as a fallback, since
        // those aren't blocked here but would still violate the constraint.
        $isUsedByActiveProduct = Product::where('base_unit_id', $base_unit->id)
            ->where('is_active', true)
            ->exists();

        if ($isUsedByActiveProduct) {
            return redirect()->route('base-units.index')
                ->with('error', 'Cannot delete this unit because it is currently in use by active products.');
        }

        try {
            $base_unit->delete();
            return redirect()->route('base-units.index')->with('success', 'Base unit deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('base-units.index')->with('error', 'Cannot delete this unit because it is still referenced by inactive products or unit conversions.');
            }

            throw $e;
        }
    }
}
