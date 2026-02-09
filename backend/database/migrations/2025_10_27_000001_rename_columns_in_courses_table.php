<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('level_id', 'academic_level_id');
            $table->renameColumn('filiere_id', 'filiaire_id');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('academic_level_id', 'level_id');
            $table->renameColumn('filiaire_id', 'filiere_id');
        });
    }
};
