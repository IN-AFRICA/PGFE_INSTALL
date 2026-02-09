<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['equipments', 'clients', 'rental_contracts', 'rental_contract_equipment', 'projects'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['equipments', 'clients', 'rental_contracts', 'rental_contract_equipment', 'projects'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'uuid')) {
                    $table->dropColumn('uuid');
                }
            });
        }
    }
};
