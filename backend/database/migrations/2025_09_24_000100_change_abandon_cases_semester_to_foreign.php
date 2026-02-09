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
        // 1) Add the new nullable foreign key column first
        if (! Schema::hasColumn('abandon_cases', 'semester_id')) {
            Schema::table('abandon_cases', function (Blueprint $table) {
                $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            });
        }

        // 2) Try to backfill semester_id from existing string column if present
        if (Schema::hasColumn('abandon_cases', 'semester')) {
            // Build a name => id map for semesters
            $semesterMap = DB::table('semesters')->pluck('id', 'name');

            // Iterate rows and map by name (works across MySQL/SQLite)
            DB::table('abandon_cases')->select('id', 'semester')->orderBy('id')->chunkById(500, function ($rows) use ($semesterMap) {
                foreach ($rows as $row) {
                    if ($row->semester === null) {
                        continue;
                    }
                    $name = (string) $row->semester;
                    $semesterId = $semesterMap[$name] ?? null;
                    if ($semesterId) {
                        DB::table('abandon_cases')->where('id', $row->id)->update(['semester_id' => $semesterId]);
                    }
                }
            });

            // 3) Drop the old string column
            Schema::table('abandon_cases', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }
    }

    public function down(): void
    {
        // 1) Recreate the old string column
        if (! Schema::hasColumn('abandon_cases', 'semester')) {
            Schema::table('abandon_cases', function (Blueprint $table) {
                $table->string('semester')->nullable();
            });
        }

        // 2) Backfill string from linked semester when possible
        if (Schema::hasColumn('abandon_cases', 'semester_id')) {
            // Build an id => name map for semesters
            $semesterMap = DB::table('semesters')->pluck('name', 'id');

            DB::table('abandon_cases')->select('id', 'semester_id')->orderBy('id')->chunkById(500, function ($rows) use ($semesterMap) {
                foreach ($rows as $row) {
                    $name = $row->semester_id ? ($semesterMap[$row->semester_id] ?? null) : null;
                    if ($name !== null) {
                        DB::table('abandon_cases')->where('id', $row->id)->update(['semester' => $name]);
                    }
                }
            });

            // 3) Drop the foreign key + column
            Schema::table('abandon_cases', function (Blueprint $table) {
                $table->dropConstrainedForeignId('semester_id');
            });
        }
    }
};
