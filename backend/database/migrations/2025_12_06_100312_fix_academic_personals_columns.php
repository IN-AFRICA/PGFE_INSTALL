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
        Schema::table('academic_personals', function (Blueprint $table) {
            // Add missing post_name if not exists
            if (!Schema::hasColumn('academic_personals', 'post_name')) {
                $table->string('post_name')->after('name');
            }
            // Rename divergent columns to match model attributes
            if (Schema::hasColumn('academic_personals', 'firstname') && !Schema::hasColumn('academic_personals', 'pre_name')) {
                $table->renameColumn('firstname', 'pre_name');
            }
            if (Schema::hasColumn('academic_personals', 'phone_number') && !Schema::hasColumn('academic_personals', 'phone')) {
                $table->renameColumn('phone_number', 'phone');
            }
            if (Schema::hasColumn('academic_personals', 'identity_card') && !Schema::hasColumn('academic_personals', 'identity_card_number')) {
                $table->renameColumn('identity_card', 'identity_card_number');
            }
            if (Schema::hasColumn('academic_personals', 'address') && !Schema::hasColumn('academic_personals', 'physical_address')) {
                $table->renameColumn('address', 'physical_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_personals', function (Blueprint $table) {
            // Revert renames and drop post_name if we added it
            if (Schema::hasColumn('academic_personals', 'pre_name') && !Schema::hasColumn('academic_personals', 'firstname')) {
                $table->renameColumn('pre_name', 'firstname');
            }
            if (Schema::hasColumn('academic_personals', 'phone') && !Schema::hasColumn('academic_personals', 'phone_number')) {
                $table->renameColumn('phone', 'phone_number');
            }
            if (Schema::hasColumn('academic_personals', 'identity_card_number') && !Schema::hasColumn('academic_personals', 'identity_card')) {
                $table->renameColumn('identity_card_number', 'identity_card');
            }
            if (Schema::hasColumn('academic_personals', 'physical_address') && !Schema::hasColumn('academic_personals', 'address')) {
                $table->renameColumn('physical_address', 'address');
            }
            if (Schema::hasColumn('academic_personals', 'post_name')) {
                $table->dropColumn('post_name');
            }
        });
    }
};
