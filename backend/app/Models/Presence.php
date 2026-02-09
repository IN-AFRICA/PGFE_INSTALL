<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Presence extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'student_id',
        'presence', // présence: true=présent, false=absent (non justifié si autres flags à 0)
        'absent_justified',
        'sick',
        'school_id',
        'classroom_id',
        'academic_level_id',
    ];

    protected $casts = [
        'presence' => 'boolean',
        'absent_justified' => 'boolean',
        'sick' => 'boolean',
        'classroom_id' => 'integer',
        'student_id' => 'integer',
        'school_id' => 'integer',
        'academic_level_id' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicLevel()
    {
        return $this->belongsTo(\App\Models\AcademicLevel::class, 'academic_level_id');
    }
}
