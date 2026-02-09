<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Registration extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory; // auto contexte
    use ScopeBySchool; // scope multi-école
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'student_id',
        'registration_date',
        'registration_status',
        'school_year_id',
        'academic_personal_id',
        'academic_level_id',
        'type_id',
        'school_id',
        'classroom_id',
        'note',
        // Ajout pour persister correctement la filière et le cycle
        'filiaire_id',
        'cycle_id',
    ];

    protected $appends = [
        'student_name',
        'classroom_name',
        'academic_level_name',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')->withTrashed();
    }

    // Relations optionnelles (facultatives) pour cohérence
    public function filiaire(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class, 'filiaire_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function getStudentNameAttribute(): ?string
    {
        return $this->student?->name;
    }

    public function getClassroomNameAttribute(): ?string
    {
        return $this->classroom?->name;
    }

    public function getAcademicLevelNameAttribute(): ?string
    {
        return $this->academicLevel?->name;
    }

    public function registrationParents()
    {
        return $this->hasOne(RegistrationParents::class, 'registration_id');
    }

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'registration_status' => 'boolean',
        ];
    }
}
