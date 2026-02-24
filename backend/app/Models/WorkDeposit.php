<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorkDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'schoolwork_planning_id',
        'file_id',
        'classroom_id',
        'student_id',
        'school_id',
        'author_id',
    ];

    public function schoolworkPlanning(): BelongsTo
    {
        return $this->belongsTo(SchoolworkPlanning::class, 'schoolwork_planning_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(PlanningFile::class, 'file_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }
}
