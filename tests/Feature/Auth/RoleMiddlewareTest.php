<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_is_blocked_from_users_management(): void
    {
        $user = User::factory()->businessAdmin()->create();

        $this->actingAs($user)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_super_admin_can_reach_users_management(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/users')
            ->assertOk();
    }

    public function test_the_users_show_route_no_longer_exists(): void
    {
        // Regression check: Route::resource('users', ...) used to register
        // a GET /users/{user} route with no corresponding controller
        // method, which threw a 500 for any Super Admin who hit it.
        $user = User::factory()->superAdmin()->create();
        $other = User::factory()->create();

        // The URI /users/{user} still exists for PUT/PATCH/DELETE (the
        // resource's update/destroy routes), so a GET there is a 405
        // Method Not Allowed, not a 404 — Laravel only 404s a URI that
        // matches no route at all for any method.
        $this->actingAs($user)
            ->get("/users/{$other->id}")
            ->assertStatus(405);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
