<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::with('company')->get();
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
            'company_id' => 'required|exists:companies,company_id', // <--- Changed from 'id' to 'company_id'
            'name' => 'required|string|max:100',
        ]);

        ProductCategory::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $category)
    {
        $companies = Company::all();
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

    public function destroy(ProductCategory $category)
    {
        try {
            $category->delete();

            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('categories.index')
                                 ->with('error', 'Cannot delete this category because it still has products assigned to it.');
            }

            throw $e;
        }
    }
}