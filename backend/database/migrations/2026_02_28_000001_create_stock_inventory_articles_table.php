<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('stock_inventory_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_inventory_id')->constrained('stock_inventories')->onDelete('cascade');
            $table->foreignId('stock_article_id')->constrained('stock_articles')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('stock_inventory_articles');
    }
};
