<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) schools: self-referential school_id pour la synchro multitenant
        if (Schema::hasTable('schools') && ! Schema::hasColumn('schools', 'school_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id')->index();
            });

            // Chaque école pointe vers elle-même
            DB::table('schools')->update(['school_id' => DB::raw('id')]);
        }

        // 2) fiche_cotations: rattacher à l'école de l'élève
        if (Schema::hasTable('fiche_cotations') && ! Schema::hasColumn('fiche_cotations', 'school_id')) {
            Schema::table('fiche_cotations', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id')->index();
            });

            // Backfill via student.school_id
            if (Schema::hasTable('students') && Schema::hasColumn('students', 'school_id')) {
                DB::statement('UPDATE fiche_cotations fc JOIN students s ON s.id = fc.student_id SET fc.school_id = s.school_id WHERE fc.school_id IS NULL');
            }
        }

        // 3) student_transfers: utiliser from_school_id comme school_id de référence
        if (Schema::hasTable('student_transfers') && ! Schema::hasColumn('student_transfers', 'school_id')) {
            Schema::table('student_transfers', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id')->index();
            });

            if (Schema::hasColumn('student_transfers', 'from_school_id')) {
                DB::statement('UPDATE student_transfers SET school_id = from_school_id WHERE school_id IS NULL');
            }
        }

        // 4) student_exits: récupérer l'école via school_years.school_id
        if (Schema::hasTable('student_exits') && ! Schema::hasColumn('student_exits', 'school_id')) {
            Schema::table('student_exits', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id')->index();
            });

            if (Schema::hasTable('school_years') && Schema::hasColumn('student_exits', 'school_year_id') && Schema::hasColumn('school_years', 'school_id')) {
                DB::statement('UPDATE student_exits se JOIN school_years sy ON sy.id = se.school_year_id SET se.school_id = sy.school_id WHERE se.school_id IS NULL');
            }
        }

        // 5) registration_parents: récupérer l'école via registrations.school_id
        if (Schema::hasTable('registration_parents') && ! Schema::hasColumn('registration_parents', 'school_id')) {
            Schema::table('registration_parents', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id')->index();
            });

            if (Schema::hasTable('registrations') && Schema::hasColumn('registration_parents', 'registration_id') && Schema::hasColumn('registrations', 'school_id')) {
                DB::statement('UPDATE registration_parents rp JOIN registrations r ON r.id = rp.registration_id SET rp.school_id = r.school_id WHERE rp.school_id IS NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schools') && Schema::hasColumn('schools', 'school_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('school_id');
            });
        }

        foreach (['fiche_cotations', 'student_transfers', 'student_exits', 'registration_parents'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'school_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('school_id');
                });
            }
        }
    }
};
