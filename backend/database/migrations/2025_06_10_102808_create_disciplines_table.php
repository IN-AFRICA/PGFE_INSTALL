<?php

declare(strict_types=1);

use App\Models\AcademicPersonal;
use App\Models\School;
use App\Models\Student;
use App\Models\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Student::class)->constrained();
            $table->foreignIdFor(School::class)->constrained();
            $table->foreignIdFor(Type::class)->constrained();
            $table->foreignIdFor(AcademicPersonal::class)->constrained();
            $table->string('title');
            $table->string('observation');
            $table->string('level');
            $table->date('discipline_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplines');
    }
};
