<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id');
            $table->foreignId('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->foreignId('base_unit_id');
            $table->decimal('unit_value', 10, 2)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};