<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('base_units', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // e.g., Kilogram, Piece, Liter
        $table->string('code')->unique(); // e.g., kg, pcs, ltr
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_units');
    }
};
