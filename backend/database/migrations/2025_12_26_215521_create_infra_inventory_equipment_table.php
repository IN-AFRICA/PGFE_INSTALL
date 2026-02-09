<?php

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
        Schema::create('infra_inventory_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('infra_inventories');
            $table->foreignId('equipment_id')->constrained('infra_equipment');
            $table->integer('quantity')->default(1);
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infra_inventory_equipment');
    }
};
