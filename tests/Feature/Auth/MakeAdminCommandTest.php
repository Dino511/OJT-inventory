<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_super_admin_on_a_fresh_database(): void
    {
        $this->artisan('make:admin')
            ->expectsQuestion('Full name', 'First Admin')
            ->expectsQuestion('Email address', 'first-admin@example.com')
            ->expectsQuestion('Password (min 8 characters, input hidden)', 'password123')
            ->expectsQuestion('Confirm password', 'password123')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'first-admin@example.com',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }

    public function test_it_rejects_mismatched_password_confirmation(): void
    {
        $this->artisan('make:admin')
            ->expectsQuestion('Full name', 'Bad Admin')
            ->expectsQuestion('Email address', 'bad-admin@example.com')
            ->expectsQuestion('Password (min 8 characters, input hidden)', 'password123')
            ->expectsQuestion('Confirm password', 'does-not-match')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'bad-admin@example.com']);
    }

    public function test_it_warns_and_can_be_cancelled_when_an_admin_already_exists(): void
    {
        User::factory()->superAdmin()->create();

        $this->artisan('make:admin')
            ->expectsConfirmation('Create another one anyway?', 'no')
            ->assertExitCode(0);

        $this->assertSame(1, User::where('role', User::ROLE_SUPER_ADMIN)->count());
    }
}
