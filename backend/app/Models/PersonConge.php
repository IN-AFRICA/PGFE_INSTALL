<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PersonConge extends Model
{
    protected $fillable = [
        'academic_personal_id',
        'jour_demand',
        'description',
        'school_id',
        'author_id',
        'school_year_id',
        'creer_a',
    ];

    public function academicPersonal()
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }
}
