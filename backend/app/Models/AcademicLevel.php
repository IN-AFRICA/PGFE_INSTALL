<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AcademicLevel extends Model
{
    use HasUuid;
    use HasFactory;
    use ScopeBySchool;

    protected $guarded = [];

    public function personals(): self|HasMany
    {
        return $this->hasMany(AcademicPersonal::class);
    }

    public function students(): self|HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'academic_level_id');
    }
}
