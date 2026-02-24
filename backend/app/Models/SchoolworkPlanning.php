<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SchoolworkPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'classroom_id',
        'course_id',
        'file_id',
        'start_date',
        'end_date',
        'school_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(PlanningFile::class, 'file_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'author_id');
    }

    public function workDeposits(): HasMany
    {
        return $this->hasMany(WorkDeposit::class, 'schoolwork_planning_id');
    }
}
