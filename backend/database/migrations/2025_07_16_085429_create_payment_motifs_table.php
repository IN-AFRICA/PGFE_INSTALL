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
        Schema::create('payment_motifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained(); // relation avec fee_types
            $table->string('name');
            $table->string('code')->unique(); // ex: janv,fev,trim-1
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_motifs');
    }
};
