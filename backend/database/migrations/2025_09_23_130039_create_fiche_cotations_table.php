<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Course;
use App\Models\SchoolYear;
// Avoid class reference to prevent case-sensitive autoload issues
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fiche_cotations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SchoolYear::class)->constrained();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Classroom::class)->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->double('note')->default(0);
            $table->double('Maxima')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiche_cotations');
    }
};
