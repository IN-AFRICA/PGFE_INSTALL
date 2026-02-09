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

final class Classroom extends Model
{
    use HasUuid;
    use AutoAssignsSchoolContext;

    use HasFactory; // auto school_id
    use ScopeBySchool; // filtrage multi-école

    protected $guarded = [];

    // Ajout des propriétés manquantes pour l'assignation
    protected $fillable = [
        'academic_level_id',
        'name',
        'indicator',
        'titulaire_id',
    ];

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

    public function visits(): HasMany|self
    {
        return $this->hasMany(Visit::class);
    }

    public function students(): HasMany|self
    {
        return $this->hasMany(Student::class);
    }

    public function titulaire(): BelongsTo
    {
        return $this->belongsTo(AcademicPersonal::class, 'titulaire_id');
    }
}
