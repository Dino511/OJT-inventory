<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
        {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('unit_value', 10, 2)->default(1)->after('base_unit_id');
            });
        }

        public function down(): void
        {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('unit_value');
            });
        }
};
