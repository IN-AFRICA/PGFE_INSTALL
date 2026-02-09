<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('account_type_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->foreignId('exchange_rate_id')->constrained();

            $table->string('beneficiary')->nullable();
            $table->string('reference')->unique();
            $table->string('expense_raison')->nullable(); // Notes ou précisions sur la dépense
            $table->double('amount');
            $table->double('amount_converted')->nullable();
            $table->text('description')->nullable();

            $table->date('expense_date')->useCurrent();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
