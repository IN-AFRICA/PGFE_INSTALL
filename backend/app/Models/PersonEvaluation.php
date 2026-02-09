<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PersonEvaluation extends Model
{
    protected $fillable = [
        'critiques',
        'c1_quantite_travail',
        'c2_theorie_pratique',
        'c3_determ_ress_perso',
        'c4_ponctualite',
        'c5_dr_att_posit_collab',
        'academic_personal_id',
        'school_year_id',
        'semester_id',
        'school_id',
        'author_id',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicPersonal()
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }
}
