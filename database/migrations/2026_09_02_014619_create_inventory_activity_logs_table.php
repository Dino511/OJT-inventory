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
        Schema::create('inventory_activity_logs', function (Blueprint $table) {
            $table->id();

            // No formal FK (same reasoning as product_activity_logs): this
            // audit trail must survive the stock record being deleted, and
            // avoids yet another SQL Server cascade-path conflict.
            $table->unsignedBigInteger('inventory_id')->nullable()->index();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->string('location_name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();

            $table->string('action', 20); // created | updated | deleted | transferred
            $table->json('changes')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_activity_logs');
    }
};
