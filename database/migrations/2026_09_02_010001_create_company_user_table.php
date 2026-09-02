<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
        });

        // Every user's existing single company_id becomes their first
        // company membership, so nobody loses access when this rolls out.
        DB::table('users')->whereNotNull('company_id')->get(['id', 'company_id'])->each(function ($user) {
            DB::table('company_user')->insert([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
