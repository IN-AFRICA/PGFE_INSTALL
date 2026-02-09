<?php

declare(strict_types=1);

use App\Models\AnnalytiqueComptability;
use App\Models\ImmoAccount;
use App\Models\ImmoSubAccount;
use App\Models\School;
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
        Schema::create('ammortissement_comptabilities', function (Blueprint $table) {
            $table->id();
            $table->string('justification');
            $table->date('date_ammortissement');
            $table->double('amount');
            $table->foreignIdFor(ImmoAccount::class);
            $table->foreignIdFor(ImmoSubAccount::class);
            $table->foreignIdFor(School::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(AnnalytiqueComptability::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ammortissement_comptabilities');
    }
};
