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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->foreignId('school_id')->constrained();
            $table->decimal('rate', 15, 6); // ex: 2275.450000 pour 1 USD = 2275.45 CDF
            $table->date('date_effective')->nullable(); // Facultatif, utile si tu veux planifier l'effet dans le futur
            $table->boolean('is_active')->default(false); // Vrai pour le taux actuellement utilisé
            $table->timestamps();

            // Index utile pour accélérer les recherches de taux actifs
            $table->unique(['currency_id', 'is_active'], 'unique_active_rate_per_currency')->where('is_active', true);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
