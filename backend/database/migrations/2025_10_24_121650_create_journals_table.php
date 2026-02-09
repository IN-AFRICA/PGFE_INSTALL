<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\InputAccount;
use App\Models\OutputAccount;
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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description');
            $table->decimal('montant', 15, 2);
            $table->foreignIdFor(InputAccount::class);
            $table->foreignIdFor(OutputAccount::class);
            $table->foreignIdFor(App\Models\AccountPlan::class);
            $table->foreignIdFor(App\Models\SubAccountPlan::class);
            $table->foreignIdFor(Account::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
