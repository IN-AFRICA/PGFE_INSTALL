<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PersonAffectation extends Model
{
    protected $fillable = [
        'academic_personal_id',
        'lieu_affectation',
        'durree_jours',
        'description',
        'date_debut',
        'school_year_id',
        'author_id',
        'school_id',
    ];

    public function academicPersonal()
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }
}
