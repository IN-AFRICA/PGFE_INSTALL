<?php

declare(strict_types=1);

use App\Models\ImmoAccount;
use App\Models\ImmoSubAccount;
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
        Schema::create('immo_ammortissemen_comptabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('model');
            $table->double('amount');
            $table->date('purchase_date');
            $table->integer('number_years');
            $table->foreignIdFor(ImmoAccount::class);
            $table->foreignIdFor(ImmoSubAccount::class);
            $table->foreignIdFor(App\Models\School::class);
            $table->foreignIdFor(App\Models\User::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immo_ammortissemen_comptabilities');
    }
};
