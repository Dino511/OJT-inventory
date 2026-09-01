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
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            // No cascade delete on either FK: both reference base_units, and SQL Server
            // rejects cascade paths that could cycle back to the same table twice.
            // Deleting a unit still in use by a conversion is blocked with a friendly
            // message instead (see UnitOfMeasureController::destroy()).
            $table->foreignId('from_unit_id')->constrained('base_units');
            $table->foreignId('to_unit_id')->constrained('base_units');
            $table->decimal('factor', 12, 4);
            $table->timestamps();

            $table->unique(['from_unit_id', 'to_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
