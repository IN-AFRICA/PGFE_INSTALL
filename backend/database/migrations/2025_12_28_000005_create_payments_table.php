<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'rental_contract_id')) {
                $table->foreignId('rental_contract_id')->nullable()->constrained('rental_contracts');
            }
            if (!Schema::hasColumn('payments', 'rental_payment_type')) {
                $table->string('rental_payment_type')->nullable();
            }
            if (!Schema::hasColumn('payments', 'rental_amount')) {
                $table->decimal('rental_amount', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('payments', 'rental_due_date')) {
                $table->date('rental_due_date')->nullable();
            }
        });
    }
    public function down(): void {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'rental_contract_id')) {
                $table->dropForeign(['rental_contract_id']);
                $table->dropColumn('rental_contract_id');
            }
            if (Schema::hasColumn('payments', 'rental_payment_type')) {
                $table->dropColumn('rental_payment_type');
            }
            if (Schema::hasColumn('payments', 'rental_amount')) {
                $table->dropColumn('rental_amount');
            }
            if (Schema::hasColumn('payments', 'rental_due_date')) {
                $table->dropColumn('rental_due_date');
            }
        });
    }
};
