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
        // On databases that predate this migration, base_unit_id's foreign key
        // still points at the legacy unit_of_measures table under this name —
        // drop it before re-adding it against base_units. A database built
        // fresh from these migrations never had that constraint in the first
        // place (products.base_unit_id is created without one), so the drop
        // is skipped there instead of failing.
        if ($this->foreignKeyExists('products_base_unit_id_foreign')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign('products_base_unit_id_foreign');
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('base_unit_id')->references('id')->on('base_units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['base_unit_id']);
            $table->foreign('base_unit_id')->references('id')->on('unit_of_measures');
        });
    }

    /**
     * The legacy constraint this migration cleans up can only exist on the
     * SQL Server databases this app has actually run on historically — a
     * database built fresh on any other driver (e.g. SQLite in the test
     * suite) never had it, so there's nothing to detect there.
     */
    private function foreignKeyExists(string $foreignKeyName): bool
    {
        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return false;
        }

        return DB::table('sys.foreign_keys')->where('name', $foreignKeyName)->exists();
    }
};
