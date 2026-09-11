<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => ProductCategory::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => null,
            'code' => fake()->unique()->bothify('SKU-####'),
            'base_unit_id' => UnitOfMeasure::factory(),
            'unit_value' => 1,
            'reorder_point' => 0,
            'selling_price' => fake()->randomFloat(2, 10, 500),
            'cost' => fake()->randomFloat(2, 5, 250),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
