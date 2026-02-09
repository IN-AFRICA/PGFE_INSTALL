<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Repechage extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_year_id',
        'filiaire_id',
        'classroom_id',
        'course_id',
        'full_name',
        'score_percent',
        'student_score',
        'is_eliminated',
        'cycle_id',
        'school_id',
        'academic_level_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function filiaire()
    {
        return $this->belongsTo(Filiaire::class);
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
