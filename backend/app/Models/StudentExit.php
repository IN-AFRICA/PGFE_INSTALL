<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class StudentExit extends Model
{
    use AutoAssignsSchoolContext;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;
    protected $fillable = [
        'date',
        'student_id',
        'exit_time',
        'motif',
        'filiere_id',
        'school_year_id',
        'semester',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
