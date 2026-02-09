<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IndisciplineCase extends Model
{
    protected $fillable = [
        'date',
        'student_id',
        'fault_count',
        'action',
        'roi',
        'classroom_id',
        'filiere_id',
        'school_year_id',
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
        // Si la colonne est 'filiere_id', on précise la clé étrangère explicitement
        return $this->belongsTo(Filiaire::class, 'filiere_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
