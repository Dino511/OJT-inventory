<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\BaseUnit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['name', 'company', 'category']) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $query = Product::with(['company', 'category', 'baseUnit']);

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'company':
                $query->orderBy(
                    Company::select('name')->whereColumn('companies.company_id', 'products.company_id'),
                    $direction
                );
                break;
            case 'category':
                $query->orderBy(
                    ProductCategory::select('name')->whereColumn('product_categories.id', 'products.category_id'),
                    $direction
                );
                break;
            default:
                $query->orderBy('created_at', $direction);
                break;
        }

        $products = $query->get();
        $conversions = UnitConversion::with('toUnit')->get()->keyBy('from_unit_id');

        return view('products.index', compact('products', 'sort', 'direction', 'conversions'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $companies = Company::all();
        $categories = ProductCategory::all();
        $baseUnits = BaseUnit::all();

        return view('products.create', compact('companies', 'categories', 'baseUnits'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'    => 'required|exists:companies,company_id',
            'category_id'   => 'nullable|exists:product_categories,id', 
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'code'          => 'required|string|unique:products,code',
            'unit_value'    => 'required|numeric|min:0',
            'base_unit_id' => 'required|exists:base_units,id',
            'reorder_point' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $companies = Company::all();
        $categories = ProductCategory::all();
        $baseUnits = BaseUnit::all();

        return view('products.edit', compact('product', 'companies', 'categories', 'baseUnits'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'company_id'    => 'required|exists:companies,company_id',
            'category_id'   => 'required|exists:product_categories,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'code'          => 'required|string|unique:products,code,' . $product->id,
            'unit_value'    => 'required|numeric|min:0',
            'base_unit_id' => 'required|exists:base_units,id',
            'reorder_point' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}