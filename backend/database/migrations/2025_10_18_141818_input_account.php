<?php

declare(strict_types=1);

use App\Models\AccountPlan;
use App\Models\User;
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
        Schema::create('InputAccount', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('amount');
            $table->string('justification');
            $table->foreignIdFor(App\Models\School::class);
            $table->foreignIdFor(AccountPlan::class);
            $table->foreignIdFor(App\Models\SubAccountPlan::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('InputAccount');
    }
};
