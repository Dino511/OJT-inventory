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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->foreignId('location_id')->constrained('locations');
            $table->string('supplier_name');
            $table->string('status')->default('pending'); // pending | received | cancelled
            $table->date('order_date');
            $table->text('notes')->nullable();
            // No cascade/null-on-delete on either FK: both reference users,
            // and SQL Server rejects multiple cascade/null paths to the same
            // table (same reasoning as unit_conversions' from/to unit FKs).
            // Deleting a user who created or received an order is blocked
            // with a friendly message instead (see UserController::destroy()).
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
