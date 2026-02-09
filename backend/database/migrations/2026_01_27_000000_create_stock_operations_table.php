<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_operations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('type', ['entrée', 'sortie']);
            $table->foreignId('article_id')->constrained('stock_articles')->onDelete('cascade');
            $table->integer('quantite');
            $table->foreignId('operateur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('academic_personal_id')->nullable()->constrained('academic_personals')->onDelete('set null');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_operations');
    }
};
