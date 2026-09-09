<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // A product now picks at most one applicable conversion, not many —
        // replace the products<->unit_conversions pivot with a single
        // nullable FK on products. Any existing links are collapsed to one
        // (the lowest id) per product before the pivot table is dropped.
        $keepers = DB::table('product_unit_conversions')
            ->select('product_id', DB::raw('MIN(unit_conversion_id) as unit_conversion_id'))
            ->groupBy('product_id')
            ->get();

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_conversion_id')->nullable()->after('base_unit_id')->constrained('unit_conversions')->nullOnDelete();
        });

        foreach ($keepers as $keeper) {
            DB::table('products')->where('id', $keeper->product_id)->update(['unit_conversion_id' => $keeper->unit_conversion_id]);
        }

        Schema::dropIfExists('product_unit_conversions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_conversion_id')->constrained('unit_conversions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'unit_conversion_id']);
        });

        $links = DB::table('products')->whereNotNull('unit_conversion_id')->get(['id', 'unit_conversion_id']);
        foreach ($links as $link) {
            DB::table('product_unit_conversions')->insert([
                'product_id' => $link->id,
                'unit_conversion_id' => $link->unit_conversion_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_conversion_id');
        });
    }
};
