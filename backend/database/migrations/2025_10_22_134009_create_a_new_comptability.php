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
        Schema::create('a_new_comptability', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\AccountPlan::class);
            $table->foreignIdFor(App\Models\SubAccountPlan::class);
            $table->double('amount');
            $table->string('type'); // 'input' or 'output'
            $table->string('justification');
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
        Schema::dropIfExists('a_new_comptability');
    }
};
