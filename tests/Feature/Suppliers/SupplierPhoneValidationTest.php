<?php

namespace Tests\Feature\Suppliers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a bug found and fixed in this codebase: the phone
 * number field hardcoded a "must be exactly 9 digits" rule even though the
 * form offers 17 different country codes, most of which don't use 9-digit
 * local numbers (e.g. US/Canada is commonly 10 digits).
 */
class SupplierPhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Supplier Co.',
            'contact_person_prefix' => 'Mr.',
            'contact_person_name' => 'John Doe',
            'phone_country_code' => '+63',
            'phone_number' => '917123456',
            'email' => 'supplier@example.com',
            'address' => '123 Main St',
        ], $overrides);
    }

    public function test_a_9_digit_philippine_number_is_accepted(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/suppliers', $this->basePayload());

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['phone' => '+63 917123456']);
    }

    public function test_a_10_digit_us_number_is_accepted(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/suppliers', $this->basePayload([
            'phone_country_code' => '+1',
            'phone_number' => '4155552671',
        ]));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['phone' => '+1 4155552671']);
    }

    public function test_a_6_digit_number_is_still_rejected_as_too_short(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/suppliers', $this->basePayload([
            'phone_number' => '12345',
        ]));

        $response->assertSessionHasErrors('phone_number');
    }

    public function test_letters_in_the_phone_number_are_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/suppliers', $this->basePayload([
            'phone_number' => '91712abcd',
        ]));

        $response->assertSessionHasErrors('phone_number');
    }
}
