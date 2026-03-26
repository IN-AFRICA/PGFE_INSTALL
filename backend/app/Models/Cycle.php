<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cycle extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory;
    use HasUuid; // auto-assignation du school_id
    use ScopeBySchool; // filtrage multi-école

    protected $guarded = [];

    public function filiaire(): BelongsTo
    {
        return $this->belongsTo(Filiaire::class, 'filiaire_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicLevels(): HasMany
    {
        return $this->hasMany(AcademicLevel::class, 'cycle_id');
    }
}
