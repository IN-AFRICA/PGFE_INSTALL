<?php

declare(strict_types=1);

use App\Models\InfraBailleur;
use App\Models\InfraCategorie;
use App\Models\School;
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
        Schema::create('infra_equipements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('date_acquisition');
            $table->double('montant_acquisition');
            $table->foreignIdFor(InfraBailleur::class)->constrained();
            $table->foreignIdFor(InfraCategorie::class)->constrained();
            $table->string('emplacement');
            $table->foreignIdFor(School::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infra_equipements');
    }
};
