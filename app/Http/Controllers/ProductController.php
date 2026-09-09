<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\BaseUnit;
use App\Models\ProductActivityLog;
use App\Models\Supplier;
use App\Models\UnitConversion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['name', 'company', 'category']) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $query = Product::with(['company', 'category.parent', 'baseUnit', 'suppliers']);

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

        $products = $query->with('unitConversion.toUnit')
            ->withCount([
                'purchaseOrderItems as pending_purchase_order_items_count' => fn ($q) => $q->whereHas(
                    'purchaseOrder', fn ($po) => $po->where('status', \App\Models\PurchaseOrder::STATUS_PENDING)
                ),
                'stockTransferItems as pending_stock_transfer_items_count' => fn ($q) => $q->whereHas(
                    'stockTransfer', fn ($st) => $st->where('status', \App\Models\StockTransfer::STATUS_PENDING)
                ),
                'inventories as stocked_inventory_count' => fn ($q) => $q->where('quantity', '>', 0),
            ])
            ->get();

        return view('products.index', compact('products', 'sort', 'direction'));
    }

    /**
     * Whether a product has "active" dependencies elsewhere in the system —
     * pending purchase orders or stock transfers that reference it, or
     * on-hand inventory — used to strengthen the delete confirmation shown
     * on the Products list rather than letting the user find out only after
     * a DB foreign-key error.
     */
    private function hasActiveDependencies(Product $product): bool
    {
        return $product->purchaseOrderItems()
                ->whereHas('purchaseOrder', fn ($q) => $q->where('status', \App\Models\PurchaseOrder::STATUS_PENDING))
                ->exists()
            || $product->stockTransferItems()
                ->whereHas('stockTransfer', fn ($q) => $q->where('status', \App\Models\StockTransfer::STATUS_PENDING))
                ->exists()
            || $product->inventories()->where('quantity', '>', 0)->exists();
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $companies = Company::all();
        $categories = ProductCategory::with('children')->topLevel()->orderBy('name')->get();
        $baseUnits = BaseUnit::all();
        $conversionOptions = $this->conversionOptions();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('companies', 'categories', 'baseUnits', 'conversionOptions', 'suppliers'));
    }

    /**
     * Every unit conversion, pre-shaped into a plain array for the create/
     * edit forms' JS picker — built here rather than inline in an @json()
     * Blade call, since a closure inside that directive's argument confuses
     * Blade's bracket-balance pre-check.
     */
    private function conversionOptions(): array
    {
        return UnitConversion::with(['fromUnit', 'toUnit'])->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'from_unit_id' => (int) $c->from_unit_id,
                'label' => '1 ' . ($c->fromUnit->name ?? '?') . ' = ' . (float) $c->factor . ' ' . ($c->toUnit->name ?? '?'),
            ])
            ->values()
            ->all();
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
            'selling_price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:4096',
            'unit_conversion_id' => [
                'nullable',
                Rule::exists('unit_conversions', 'id')->where('from_unit_id', $request->input('base_unit_id')),
            ],
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'integer|exists:suppliers,id',
        ]);

        $supplierIds = $validated['supplier_ids'] ?? [];
        unset($validated['supplier_ids']);

        // The "None" radio submits an empty string, not an absent field —
        // coerce explicitly rather than relying on middleware to have
        // already converted it, since this FK column rejects ''.
        $validated['unit_conversion_id'] = $validated['unit_conversion_id'] ?: null;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create($validated);
        $product->suppliers()->sync($supplierIds);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['company', 'category.parent', 'baseUnit', 'inventories.location', 'unitConversion.toUnit']);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $companies = Company::all();
        $categories = ProductCategory::with('children')->topLevel()->orderBy('name')->get();
        $baseUnits = BaseUnit::all();
        $conversionOptions = $this->conversionOptions();
        $suppliers = Supplier::orderBy('name')->get();
        $selectedSupplierIds = $product->suppliers->pluck('id')->all();

        return view('products.edit', compact(
            'product', 'companies', 'categories', 'baseUnits',
            'conversionOptions', 'suppliers', 'selectedSupplierIds'
        ));
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
            'selling_price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:4096',
            'remove_photo' => 'nullable|boolean',
            'unit_conversion_id' => [
                'nullable',
                Rule::exists('unit_conversions', 'id')->where('from_unit_id', $request->input('base_unit_id')),
            ],
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'integer|exists:suppliers,id',
        ]);

        $supplierIds = $validated['supplier_ids'] ?? [];
        unset($validated['supplier_ids']);

        // The "None" radio submits an empty string, not an absent field —
        // coerce explicitly rather than relying on middleware to have
        // already converted it, since this FK column rejects ''.
        $validated['unit_conversion_id'] = $validated['unit_conversion_id'] ?: null;

        $removePhoto = ! empty($validated['remove_photo']);
        unset($validated['remove_photo']);

        if ($request->hasFile('photo')) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        } elseif ($removePhoto) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $validated['photo'] = null;
        }

        $product->update($validated);
        $product->suppliers()->sync($supplierIds);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Toggle a product between active and inactive. Inactive products are
     * hidden from the "add new stock" pickers (Inventory create/edit,
     * Transfer's "Add Another Product") but stay visible here and keep
     * their existing stock records untouched.
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return redirect()->route('products.index')
            ->with('success', 'Product ' . ($product->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->inventories()->where('quantity', '>', 0)->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete this product because it still has stock on hand. Adjust or transfer out its remaining stock first.');
        }

        try {
            $photo = $product->photo;

            $product->delete();

            if ($photo) {
                Storage::disk('public')->delete($photo);
            }

            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('products.index')
                                 ->with('error', 'Cannot delete this product because it still has stock records or sales associated with it.');
            }

            throw $e;
        }
    }

    /**
     * Full activity log across every product (including deleted ones,
     * which is why this isn't scoped to a single Product model binding).
     */
    public function historyIndex()
    {
        $logs = ProductActivityLog::orderByDesc('created_at')->get();

        // A log row's own action (e.g. "created") says nothing about whether
        // that product was deleted *later* — check current existence directly
        // so "View product history" never links to a since-deleted product.
        $existingProductIds = Product::whereIn('id', $logs->pluck('product_id')->filter()->unique())
            ->pluck('id')
            ->all();

        return view('products.history_index', compact('logs', 'existingProductIds'));
    }

    /**
     * Activity log for a single, still-existing product.
     */
    public function history(Product $product)
    {
        $logs = ProductActivityLog::where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->get();

        return view('products.history', compact('product', 'logs'));
    }
}