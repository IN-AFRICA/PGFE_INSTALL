<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('daily_price', 10, 2);
            $table->string('status')->default('active');
            $table->foreignId('school_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('equipments');
    }
};
