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
        if (! Schema::hasTable('visits')) {
            Schema::create('visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('personal_id')->constrained('academic_personals')->onDelete('cascade');
                $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
                $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
                $table->string('subject');
                $table->double('cot_doc_prof');
                $table->double('cot_meth_proc');
                $table->double('cot_matiere');
                $table->double('cot_march_lecon');
                $table->double('cot_enseignant');
                $table->double('cot_eleve');
                $table->integer('visit_hour');
                $table->text('summary');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
