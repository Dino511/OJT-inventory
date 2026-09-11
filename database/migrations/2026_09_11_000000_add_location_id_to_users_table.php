<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User::location() and UserController have relied on a `location_id`
 * column on `users` since Moderator POS-location locking was added, but no
 * migration in this repo ever actually created it — a fresh install built
 * from these migrations alone throws "no such column: location_id" the
 * first time a user is created or updated. This adds it, matching the
 * existing nullable `company_id` FK's pattern (set null on delete, so
 * removing a location doesn't cascade-delete the user).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'location_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('company_id')
                ->constrained('locations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
