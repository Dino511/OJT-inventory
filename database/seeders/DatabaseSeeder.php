<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create(['name' => 'Main Warehouse Corp']);

        $category = ProductCategory::create([
            'company_id' => $company->id,
            'name' => 'Electronics'
        ]);

        $unit = UnitOfMeasure::create(['name' => 'Piece', 'symbol' => 'pc']);

        Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'ELEC-001',
            'name' => 'Wireless Mouse',
            'description' => 'Ergonomic optical mouse',
            'base_unit_id' => $unit->id,
            'reorder_point' => 10.00,
            'is_active' => true,
        ]);
    }
}