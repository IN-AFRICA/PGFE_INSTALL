<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // LOCATION_EQUIPMENT -> equipments
        Schema::table('equipments', function (Blueprint $table) {
            if (! Schema::hasColumn('equipments', 'equipment_code')) {
                $table->string('equipment_code')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('equipments', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('name');
            }
            if (! Schema::hasColumn('equipments', 'mark_model')) {
                $table->string('mark_model')->nullable()->after('serial_number');
            }
            if (! Schema::hasColumn('equipments', 'tech_specification')) {
                $table->text('tech_specification')->nullable()->after('description');
            }
            if (! Schema::hasColumn('equipments', 'comments')) {
                $table->text('comments')->nullable()->after('tech_specification');
            }
            if (! Schema::hasColumn('equipments', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('status');
            }
        });

        // LOCATION_TENANT -> clients
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'tenant_code')) {
                $table->string('tenant_code')->nullable()->unique()->after('id');
            }
        });

        // LOCATION_PROJECT -> projects
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'project_code')) {
                $table->string('project_code')->nullable()->unique()->after('id');
            }
        });

        // LOCATION_CONTRACT -> rental_contracts
        Schema::table('rental_contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_contracts', 'contract_code')) {
                $table->string('contract_code')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('rental_contracts', 'loan_term')) {
                $table->integer('loan_term')->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('rental_contracts', 'interest_rate')) {
                $table->decimal('interest_rate', 8, 2)->nullable()->after('loan_term');
            }
            if (! Schema::hasColumn('rental_contracts', 'period_genre')) {
                $table->integer('period_genre')->nullable()->after('interest_rate');
            }
            if (! Schema::hasColumn('rental_contracts', 'loan_start_date')) {
                $table->date('loan_start_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('rental_contracts', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects');
            }
        });

        // LOCATION_CONTRACT_EQUIPMENT -> rental_contract_equipment
        Schema::table('rental_contract_equipment', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_contract_equipment', 'contract_equipment_code')) {
                $table->string('contract_equipment_code')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('rental_contract_equipment', 'is_hand_over')) {
                $table->boolean('is_hand_over')->default(false)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rental_contract_equipment', function (Blueprint $table) {
            $table->dropColumn(['contract_equipment_code', 'is_hand_over']);
        });

        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['contract_code', 'loan_term', 'interest_rate', 'period_genre', 'loan_start_date']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('project_code');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('tenant_code');
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->dropColumn(['equipment_code', 'serial_number', 'mark_model', 'tech_specification', 'comments', 'is_available']);
        });
    }
};
