<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * A fresh database has zero users and no "first-run setup" page — this is
 * the only way to get a working login on a brand-new install (e.g. a
 * server this app was just deployed to) without hand-writing SQL.
 */
class MakeAdminCommand extends Command
{
    protected $signature = 'make:admin';

    protected $description = 'Create a Super Admin account (for first-time setup on a fresh database)';

    public function handle(): int
    {
        $existingAdmins = User::where('role', User::ROLE_SUPER_ADMIN)->count();

        if ($existingAdmins > 0) {
            $this->warn("This database already has {$existingAdmins} Super Admin account(s).");

            if (! $this->confirm('Create another one anyway?', false)) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }
        }

        $name = $this->ask('Full name');
        $email = $this->ask('Email address');
        $password = $this->secret('Password (min 8 characters, input hidden)');
        $passwordConfirmation = $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->info("Super Admin '{$user->name}' <{$user->email}> created successfully.");

        return self::SUCCESS;
    }
}
