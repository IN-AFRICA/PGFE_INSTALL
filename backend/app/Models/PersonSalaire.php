<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PersonSalaire extends Model
{
    protected $fillable = [
        'mois_id',
        'montant',
        'school_year_id',
        'description',
        'author_id',
        'academic_personal_id', // le personnel payé
    ];
    public function academicPersonal()
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    public function author()
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }

    public function mois()
    {
        return $this->belongsTo(Mois::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
