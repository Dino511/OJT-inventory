<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill any existing rows so the columns can be made NOT NULL.
        DB::table('suppliers')->whereNull('contact_person')->update(['contact_person' => 'N/A']);
        DB::table('suppliers')->whereNull('phone')->update(['phone' => 'N/A']);
        DB::table('suppliers')->whereNull('address')->update(['address' => 'N/A']);

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            $table->string('contact_person', 150)->nullable(false)->change();
            $table->string('phone', 30)->nullable(false)->change();
            $table->string('address', 500)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');

            $table->string('contact_person', 150)->nullable()->change();
            $table->string('phone', 30)->nullable()->change();
            $table->string('address', 500)->nullable()->change();
        });
    }
};
