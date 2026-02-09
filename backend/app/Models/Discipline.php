<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DisciplineLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Discipline extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'academic_personal_id');
    }

    protected function casts(): array
    {
        return [
            'discipline_date' => 'date',
            'level' => DisciplineLevelEnum::class,
        ];
    }
}
