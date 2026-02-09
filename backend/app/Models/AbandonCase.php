<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AbandonCase extends Model
{
    protected $fillable = [
        'school_year_id',
        'classroom_id',
        'filiere_id',
        'semester_id',
        'student_id',
        'comment',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function filiaire(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        // Use the proper Semester model (casing matters)
        return $this->belongsTo(Semester::class);
    }
}
