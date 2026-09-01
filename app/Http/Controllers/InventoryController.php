<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['product', 'location', 'quantity']) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $query = Inventory::with(['product', 'location']);

        switch ($sort) {
            case 'product':
                $query->orderBy(
                    Product::select('name')->whereColumn('products.id', 'inventories.product_id'),
                    $direction
                );
                break;
            case 'location':
                $query->orderBy(
                    Location::select('name')->whereColumn('locations.id', 'inventories.location_id'),
                    $direction
                );
                break;
            case 'quantity':
                $query->orderBy('quantity', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
                break;
        }

        $inventories = $query->get();

        return view('inventory.index', compact('inventories', 'sort', 'direction'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('inventory.create', compact('products', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('inventories')->where(fn ($query) => $query->where('location_id', $request->location_id)),
            ],
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:0',
        ], [
            'product_id.unique' => 'This product already has a stock record at the selected location. Edit that record instead.',
        ]);

        Inventory::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory record added successfully.');
    }

    public function edit(Inventory $inventory)
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('inventory.edit', compact('inventory', 'products', 'locations'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('inventories')
                    ->where(fn ($query) => $query->where('location_id', $request->location_id))
                    ->ignore($inventory->id),
            ],
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:0',
        ], [
            'product_id.unique' => 'This product already has a stock record at the selected location. Edit that record instead.',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory record updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory record deleted successfully.');
    }
}
