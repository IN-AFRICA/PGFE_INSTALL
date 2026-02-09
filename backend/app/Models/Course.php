<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Course extends Model
{
    use HasUuid;
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;

    protected $fillable = [
        'label',
        'cycle_id',
        'academic_level_id',
        'filiaire_id',
        'school_id',
        'classroom_id',
        'hourly_volume',
        'max_period_1',
        'max_period_2',
        'max_period_3',
        'max_period_4',
        'max_exam_1',
        'max_exam_2',
        'maxima',
        'created_at',
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function filiaire()
    {
        return $this->belongsTo(Filiaire::class, 'filiaire_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }


    public function academicPersonals()
    {
        return $this->belongsToMany(AcademicPersonal::class, 'academic_personal_course', 'course_id', 'academic_personal_id');
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

}
