<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_activity_logs', function (Blueprint $table) {
            $table->id();

            // No formal FK (same audit-trail pattern as the other activity
            // log tables): must survive the company being deleted.
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('company_name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();

            $table->string('action', 20); // created | updated | deleted
            $table->json('changes')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_activity_logs');
    }
};
