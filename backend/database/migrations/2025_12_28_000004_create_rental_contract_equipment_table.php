<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rental_contract_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_contract_id')->constrained();
            $table->foreignId('equipment_id')->constrained('equipments');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->foreignId('school_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rental_contract_equipment');
    }
};
