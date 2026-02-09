<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FicheCotation extends Model
{
    protected $fillable = [
        'school_year_id',
        'student_id',
        'classroom_id',
        'semester_id',
        'course_id',
        'note',
        'Maxima',
    ];

    protected $casts = [
        // 'note' => 'float', // SUPPRIMÉ : on ne caste plus en float
        'Maxima' => 'float',
        // Optionnel :
        // 'note' => 'array', // décommente ceci si tu veux que Laravel décode automatiquement le JSON
    ];

    // Relations
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function registration()
    {
        // Si tu as une clé registration_id dans la table fiche_cotations :
        // return $this->belongsTo(Registration::class);
        // Sinon, relation personnalisée via student_id et classroom_id :
        return $this->hasOne(Registration::class, 'student_id', 'student_id')
            ->whereColumn('classroom_id', 'classroom_id');
    }
}
