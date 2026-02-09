<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table `rental_sessions` is already handled by an earlier migration
        // (2026_02_07_000200_update_rental_schema_per_spec), so nothing to do here.
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_sessions');
    }
};
