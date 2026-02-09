<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Province::class)->constrained();
            $table->foreignIdFor(App\Models\Territory::class)->constrained();
            $table->foreignIdFor(App\Models\Commune::class)->constrained();
            $table->foreignIdFor(App\Models\Parents::class)->nullable()->constrained();
            $table->string('matricule')->unique();
            $table->string('name');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('gender');
            $table->string('civil_status');
            $table->string('address');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('students');
    }
};
