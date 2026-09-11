<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->city(),
            'description' => null,
            'type' => Location::TYPE_STORE,
            'is_active' => true,
        ];
    }

    public function warehouse(): static
    {
        return $this->state(fn () => ['type' => Location::TYPE_WAREHOUSE]);
    }

    public function asStore(): static
    {
        return $this->state(fn () => ['type' => Location::TYPE_STORE]);
    }
}
