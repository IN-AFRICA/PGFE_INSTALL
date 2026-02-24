<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class StudentActivity extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use ScopeBySchool;
    use SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'school_activity_id',
        'classroom_id',
        'school_id',
        'author_id',
    ];

    public function schoolActivity(): BelongsTo
    {
        return $this->belongsTo(SchoolActivity::class, 'school_activity_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
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
