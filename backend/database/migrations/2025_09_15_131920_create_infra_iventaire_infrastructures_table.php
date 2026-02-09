<?php

declare(strict_types=1);

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
        Schema::create('infra_iventaire_infrastructures', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\InfraInfrastructure::class)->constrained();
            $table->text('description')->nullable();
            $table->foreignIdFor(App\Models\School::class)->constrained();
            $table->foreignIdFor(App\Models\AcademicPersonal::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infra_iventaire_infrastructures');
    }
};
