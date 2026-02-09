<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Supprime toutes les registrations dont le student_id ne correspond à aucun étudiant (même soft deleted)
        DB::statement('
            DELETE FROM registrations
            WHERE student_id IS NOT NULL
            AND student_id NOT IN (SELECT id FROM students)
        ');
    }

    public function down(): void
    {
        // Non réversible
    }
};
