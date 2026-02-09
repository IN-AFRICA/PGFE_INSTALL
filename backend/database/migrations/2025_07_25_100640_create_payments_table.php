<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('fee_id')->constrained('fees');
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('payment_motif_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->foreignId('exchange_rate_id')->constrained();
            $table->foreignId('account_type_id')->constrained();

            $table->double('amount', 15, 2); // Montant payé en devise locale
            $table->double('amount_foreign', 15, 2); // Montant dans la devise utilisée
            $table->double('remaining_amount', 15, 2)->default(0); // Reste à payer
            $table->string('reference')->unique();

            $table->text('details')->nullable(); // Notes ou précisions sur le paiement
            $table->timestamp('paid_at')->useCurrent();

            $table->enum('status', ['pending', 'confirmed', 'refunded'])->default('confirmed'); // Statut du paiement
            $table->timestamp('confirmed_at')->useCurrent();
            $table->timestamp('refunded_at')->nullable(); // Date du remboursement, si applicable

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}
