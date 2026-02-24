<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class StudentTransfer extends Model
{
    use AutoAssignsSchoolContext;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'student_id',
        'from_school_id',
        'to_school_id',
        'from_classroom_id',
        'to_classroom_id',
        'school_year_id',
        'transfer_date',
        'reason',
        'created_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'from_school_id');
    }

    public function toSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'to_school_id');
    }

    public function fromClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'from_classroom_id');
    }

    public function toClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }
}
