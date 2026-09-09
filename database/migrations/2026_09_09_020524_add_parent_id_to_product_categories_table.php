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
        Schema::table('product_categories', function (Blueprint $table) {
            // Self-referencing, nullable — a category with parent_id set is
            // a subcategory of that row; null means it's a top-level
            // category. No cascade/null-on-delete: deleting a parent that
            // still has subcategories is explicitly blocked in
            // ProductCategoryController::destroy() with a clear message,
            // backed by this FK as a hard safety net.
            $table->foreignId('parent_id')->nullable()->after('company_id')
                ->constrained('product_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
