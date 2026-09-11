<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_user(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New Hire',
            'email' => 'new-hire@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_BUSINESS_ADMIN,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new-hire@example.com',
            'role' => User::ROLE_BUSINESS_ADMIN,
        ]);
    }

    public function test_a_user_cannot_remove_their_own_super_admin_role(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_BUSINESS_ADMIN,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame(User::ROLE_SUPER_ADMIN, $admin->fresh()->role);
    }

    public function test_a_user_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->patch("/users/{$admin->id}/toggle-status");

        $response->assertRedirect(route('users.index'));
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_a_user_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_can_deactivate_another_user(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $other = User::factory()->businessAdmin()->create();

        $this->actingAs($admin)->patch("/users/{$other->id}/toggle-status");

        $this->assertFalse($other->fresh()->is_active);
    }

    public function test_email_must_be_unique_when_creating_a_user(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $existing = User::factory()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MODERATOR,
        ]);

        $response->assertSessionHasErrors('email');
    }
}
