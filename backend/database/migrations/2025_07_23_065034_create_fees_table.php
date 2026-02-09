<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();

            $table->string('label');
            $table->double('amount');

            $table->foreignId('currency_id')->constrained();

            $table->foreignId('fee_type_id')
                ->constrained();

            $table->foreignId('school_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('effective_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
