<?php

declare(strict_types=1);

use App\Models\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Country::class)->constrained();
            $table->string('city');
            $table->string('name')->unique();
            $table->string('address');
            $table->string('latitude')->nullable();
            $table->foreignIdFor(Type::class)->constrained();

            $table->string('longitude')->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
