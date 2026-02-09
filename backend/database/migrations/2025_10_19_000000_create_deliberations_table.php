<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliberations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Student::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\Classroom::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\Filiaire::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\AcademicLevel::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\Cycle::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\SchoolYear::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\School::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(App\Models\Course::class)->constrained()->onDelete('cascade');
            $table->boolean('is_validated')->default(false);
            $table->foreignIdFor(App\Models\ConduiteGrade::class)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->index(['school_year_id', 'classroom_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberations');
    }
};
