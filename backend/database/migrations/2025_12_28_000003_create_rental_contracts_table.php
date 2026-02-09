<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->foreignId('school_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rental_contracts');
    }
};
