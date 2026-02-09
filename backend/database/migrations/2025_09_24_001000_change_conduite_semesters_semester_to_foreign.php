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
        // Add semester_id if not exists
        if (! Schema::hasColumn('conduite_semesters', 'semester_id')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete()->after('school_year_id');
            });
        }

        // Backfill from old string column if present
        if (Schema::hasColumn('conduite_semesters', 'semester')) {
            $map = DB::table('semesters')->pluck('id', 'name');
            DB::table('conduite_semesters')->select('id', 'semester')->orderBy('id')->chunkById(500, function ($rows) use ($map) {
                foreach ($rows as $row) {
                    if ($row->semester === null) {
                        continue;
                    }
                    $name = (string) $row->semester;
                    $id = $map[$name] ?? null;
                    if ($id) {
                        DB::table('conduite_semesters')->where('id', $row->id)->update(['semester_id' => $id]);
                    }
                }
            });
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }

        // Drop date column if it exists (schema drift)
        if (Schema::hasColumn('conduite_semesters', 'date')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
    }

    public function down(): void
    {
        // Recreate string semester
        if (! Schema::hasColumn('conduite_semesters', 'semester')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->string('semester')->nullable()->after('school_year_id');
            });
        }

        // Backfill from FK if present
        if (Schema::hasColumn('conduite_semesters', 'semester_id')) {
            $map = DB::table('semesters')->pluck('name', 'id');
            DB::table('conduite_semesters')->select('id', 'semester_id')->orderBy('id')->chunkById(500, function ($rows) use ($map) {
                foreach ($rows as $row) {
                    $name = $row->semester_id ? ($map[$row->semester_id] ?? null) : null;
                    if ($name !== null) {
                        DB::table('conduite_semesters')->where('id', $row->id)->update(['semester' => $name]);
                    }
                }
            });
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('semester_id');
            });
        }

        // Optionally recreate date (keeping nullable to avoid data issues)
        if (! Schema::hasColumn('conduite_semesters', 'date')) {
            Schema::table('conduite_semesters', function (Blueprint $table) {
                $table->date('date')->nullable();
            });
        }
    }
};
