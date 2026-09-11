<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'contact_person' => fake()->name(),
            'secondary_contact_person' => null,
            'phone' => fake()->numerify('9########'),
            'secondary_phone' => null,
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
        ];
    }
}
