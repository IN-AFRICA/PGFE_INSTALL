<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop old foreign keys and columns if they exist
            if (Schema::hasColumn('payments', 'exchange_rate_id')) {
                $table->dropConstrainedForeignId('exchange_rate_id');
            }
            if (Schema::hasColumn('payments', 'account_type_id')) {
                $table->dropConstrainedForeignId('account_type_id');
            }
            if (Schema::hasColumn('payments', 'payment_motif_id')) {
                $table->dropConstrainedForeignId('payment_motif_id');
            }
            if (Schema::hasColumn('payments', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('payments', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
            // Supprimer la colonne amount_foreign (ce n'est pas une clé étrangère)
            if (Schema::hasColumn('payments', 'amount_foreign')) {
                $table->dropColumn('amount_foreign');
            }

            // Ajouts des nouvelles relations
            if (! Schema::hasColumn('payments', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'classroom_id')) {
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('payments', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }
            if (Schema::hasColumn('payments', 'classroom_id')) {
                $table->dropConstrainedForeignId('classroom_id');
            }
            if (Schema::hasColumn('payments', 'school_year_id')) {
                $table->dropConstrainedForeignId('school_year_id');
            }

            if (! Schema::hasColumn('payments', 'school_id')) {
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('payments', 'user_id')) {
                $table->foreignId('user_id')->constrained();
            }
            if (! Schema::hasColumn('payments', 'payment_motif_id')) {
                $table->foreignId('payment_motif_id')->constrained();
            }
            if (! Schema::hasColumn('payments', 'exchange_rate_id')) {
                $table->foreignId('exchange_rate_id')->constrained();
            }
            if (! Schema::hasColumn('payments', 'account_type_id')) {
                $table->foreignId('account_type_id')->constrained();
            }
            // Réintroduire amount_foreign en double(15,2)
            if (! Schema::hasColumn('payments', 'amount_foreign')) {
                $table->double('amount_foreign', 15, 2)->default(0);
            }
        });
    }
};
