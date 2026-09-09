<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::with(['company', 'parent', 'children'])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $companies = Company::all();

        return view('categories.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,company_id',
            'name' => 'required|string|max:100',
            'subcategory_name' => 'nullable|string|max:100',
        ]);

        $category = DB::transaction(function () use ($validated) {
            $category = ProductCategory::create([
                'company_id' => $validated['company_id'],
                'name' => $validated['name'],
            ]);

            if (! empty($validated['subcategory_name'])) {
                ProductCategory::create([
                    'company_id' => $validated['company_id'],
                    'parent_id' => $category->id,
                    'name' => $validated['subcategory_name'],
                ]);
            }

            return $category;
        });

        $message = ! empty($validated['subcategory_name'])
            ? 'Category and subcategory created successfully.'
            : 'Category created successfully.';

        return redirect()->route('categories.index')->with('success', $message);
    }

    public function edit(ProductCategory $category)
    {
        $companies = Company::all();
        $category->load('children', 'parent');

        return view('categories.edit', compact('category', 'companies'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,company_id',
            'name' => 'required|string|max:100',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Adds one subcategory under an existing (top-level) category — the
     * same one-at-a-time pattern as the optional field on the Create form,
     * exposed again on Edit for categories created before a subcategory
     * was added, or that need another one later.
     */
    public function storeSubcategory(Request $request, ProductCategory $category)
    {
        if ($category->isSubcategory()) {
            return redirect()->route('categories.edit', $category)
                ->with('error', 'A subcategory cannot itself have subcategories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        ProductCategory::create([
            'company_id' => $category->company_id,
            'parent_id' => $category->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('categories.edit', $category)->with('success', 'Subcategory added successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        // A subcategory is only ever deleted from its parent's Edit page
        // (see categories/edit.blade.php), so every redirect here — success
        // or error — should land back on that same page instead of the
        // main Categories list, which would otherwise silently drop the
        // user out of the screen they were just working in.
        $redirectRoute = $category->isSubcategory()
            ? redirect()->route('categories.edit', $category->parent_id)
            : redirect()->route('categories.index');

        if ($category->children()->exists()) {
            return $redirectRoute
                ->with('error', 'Cannot delete this category because it still has subcategories. Delete or reassign those first.');
        }

        try {
            $category->delete();

            return $redirectRoute->with('success', 'Category deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return $redirectRoute
                                 ->with('error', 'Cannot delete this category because it still has products assigned to it.');
            }

            throw $e;
        }
    }
}
