<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Deliberation extends Model
{
    protected $fillable = [
        'student_id',
        'classroom_id',
        'filiaire_id',
        'academic_level_id',
        'cycle_id',
        'school_year_id',
        'school_id',
        'course_id',
        'is_validated', // champ booléen pour valider la délibération
        'conduite_grade_id', // relation à la conduite (mention)
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function filiaire(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class);
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function conduiteGrade(): BelongsTo
    {
        return $this->belongsTo(ConduiteGrade::class, 'conduite_grade_id');
    }

    // Les côtes (notes) dans un cours
    public function cotations(): HasMany
    {
        return $this->hasMany(FicheCotation::class);
    }

    public function registration()
    {
        // Relation à l'inscription de l'élève dans la classe
        return $this->belongsTo(Registration::class, 'student_id', 'student_id')
            ->whereColumn('classroom_id', 'classroom_id');
    }
}
