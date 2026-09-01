<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
{
    Schema::table('unit_of_measures', function (Blueprint $table) {
        if (!Schema::hasColumn('unit_of_measures', 'code')) {
            $table->string('code', 50)->nullable(); // Add ->nullable() here
        }
    });
}

public function down(): void
{
    Schema::table('unit_of_measures', function (Blueprint $table) {
        $table->dropColumn('code');
    });
}
};
