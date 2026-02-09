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
                $table->string('equipment_code')->nullable()->after('id');
            }
            if (! Schema::hasColumn('equipments', 'label')) {
                $table->string('label')->nullable()->after('equipment_code');
            }
            if (! Schema::hasColumn('equipments', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('label');
            }
            if (! Schema::hasColumn('equipments', 'mark_model')) {
                $table->string('mark_model')->nullable()->after('serial_number');
            }
            if (! Schema::hasColumn('equipments', 'tech_specification')) {
                $table->text('tech_specification')->nullable()->after('mark_model');
            }
            if (! Schema::hasColumn('equipments', 'price_value')) {
                $table->decimal('price_value', 15, 2)->nullable()->after('tech_specification');
            }
            if (! Schema::hasColumn('equipments', 'comments')) {
                $table->text('comments')->nullable()->after('description');
            }
            if (! Schema::hasColumn('equipments', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('status');
            }
        });

        // LOCATION_TENANT -> clients
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'tenant_code')) {
                $table->string('tenant_code')->nullable()->after('id');
            }
            if (! Schema::hasColumn('clients', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('phone');
            }
        });

        // LOCATION_PROJECT -> projects
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'project_code')) {
                $table->string('project_code')->nullable()->after('id');
            }
        });

        // LOCATION_CONTRACT -> rental_contracts
        Schema::table('rental_contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_contracts', 'contract_code')) {
                $table->string('contract_code')->nullable()->after('id');
            }
            if (! Schema::hasColumn('rental_contracts', 'amount')) {
                $table->decimal('amount', 15, 2)->nullable()->after('contract_code');
            }
            if (! Schema::hasColumn('rental_contracts', 'loan_term')) {
                $table->integer('loan_term')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('rental_contracts', 'interest_rate')) {
                $table->decimal('interest_rate', 8, 2)->nullable()->after('loan_term');
            }
            if (! Schema::hasColumn('rental_contracts', 'period_genre')) {
                $table->integer('period_genre')->nullable()->after('interest_rate');
            }
            if (! Schema::hasColumn('rental_contracts', 'loan_start_date')) {
                $table->dateTime('loan_start_date')->nullable()->after('period_genre');
            }
            // Liens vers tenant (client) et projet existent déjà partiellement via client_id
            if (! Schema::hasColumn('rental_contracts', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->after('client_id');
            }
        });

        // LOCATION_CONTRACT_EQUIPMENT -> rental_contract_equipment
        Schema::table('rental_contract_equipment', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_contract_equipment', 'contract_equipment_code')) {
                $table->string('contract_equipment_code')->nullable()->after('id');
            }
            if (! Schema::hasColumn('rental_contract_equipment', 'is_hand_over')) {
                $table->boolean('is_hand_over')->default(false)->after('price');
            }
        });

        // LOCATION_SESSION -> nouvelle table rental_sessions
        if (! Schema::hasTable('rental_sessions')) {
            Schema::create('rental_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable()->unique();
                $table->string('session_code')->nullable();
                $table->foreignId('equipment_id')->constrained('equipments');
                $table->foreignId('client_id')->constrained('clients');
                $table->string('status')->default('pending');
                $table->text('description')->nullable();
                $table->foreignId('school_id')->constrained();
                $table->foreignId('user_id')->constrained();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            foreach (['equipment_code','label','serial_number','mark_model','tech_specification','price_value','comments','is_available'] as $col) {
                if (Schema::hasColumn('equipments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            foreach (['tenant_code','phone_number'] as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'project_code')) {
                $table->dropColumn('project_code');
            }
        });

        Schema::table('rental_contracts', function (Blueprint $table) {
            foreach (['contract_code','amount','loan_term','interest_rate','period_genre','loan_start_date'] as $col) {
                if (Schema::hasColumn('rental_contracts', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('rental_contracts', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
        });

        Schema::table('rental_contract_equipment', function (Blueprint $table) {
            foreach (['contract_equipment_code','is_hand_over'] as $col) {
                if (Schema::hasColumn('rental_contract_equipment', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('rental_sessions')) {
            Schema::dropIfExists('rental_sessions');
        }
    }
};
