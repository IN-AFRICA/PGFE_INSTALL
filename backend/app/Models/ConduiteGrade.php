<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConduiteGrade extends Model
{
    protected $fillable = [
        'school_year_id',
        'filiere_id',
        'classroom_id',
        'student_id',
        'fault_count',
        'conduite_semester_1_id',
        'conduite_semester_2_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'school_year_id' => 'integer',
        'filiere_id' => 'integer',
        'classroom_id' => 'integer',
        'student_id' => 'integer',
        'fault_count' => 'integer',
        'conduite_semester_1_id' => 'integer',
        'conduite_semester_2_id' => 'integer',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function conduiteSemester1(): BelongsTo
    {
        return $this->belongsTo(ConduiteSemester::class, 'conduite_semester_1_id');
    }

    public function conduiteSemester2(): BelongsTo
    {
        return $this->belongsTo(ConduiteSemester::class, 'conduite_semester_2_id');
    }
}
