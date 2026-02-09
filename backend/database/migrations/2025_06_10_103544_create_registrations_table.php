<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\School::class)->constrained();
            $table->foreignIdFor(App\Models\Classroom::class)->constrained();
            $table->foreignIdFor(App\Models\Student::class)->constrained();
            $table->foreignIdFor(App\Models\SchoolYear::class)->constrained();
            $table->foreignIdFor(App\Models\AcademicPersonal::class)->constrained();
            $table->foreignIdFor(App\Models\AcademicLevel::class)->constrained();
            $table->foreignId('type_id')->constrained();
            $table->date('registration_date');
            $table->boolean('registration_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
