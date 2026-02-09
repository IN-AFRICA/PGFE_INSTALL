<?php

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory, AutoAssignsSchoolContext, ScopeBySchool;

    protected $fillable = [
        'school_id',
        'school_year_id',
        'classroom_id',
        'academic_personal_id',
        'course_id',
        'day',
        'start_time',
        'end_time',
        'week_number',
    ];

    public function academicPersonal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
