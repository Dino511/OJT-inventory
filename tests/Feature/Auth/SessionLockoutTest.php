<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a bug found and fixed in this codebase: a role or
 * active-status change made to an ALREADY-LOGGED-IN user used to only be
 * enforced on their next fresh login (AuthController::login) — an existing
 * session kept full access until it happened to expire or the user logged
 * out. EnsureActive now re-checks both conditions on every request.
 */
class SessionLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_demoting_a_logged_in_user_to_moderator_ends_their_session_on_the_next_request(): void
    {
        $user = User::factory()->businessAdmin()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        // Simulate a Super Admin demoting this still-logged-in user, exactly
        // as UserController::update would (without going through the full
        // authorization/validation stack, since that's not what's under
        // test here).
        $user->update(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivating_a_logged_in_user_ends_their_session_on_the_next_request(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        $user->update(['is_active' => false]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_an_unaffected_logged_in_user_keeps_normal_access(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        // Reload the same still-valid user on a second request — must NOT
        // be a false-positive lockout.
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
