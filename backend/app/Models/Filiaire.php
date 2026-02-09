<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Filiaire extends Model
{
    use HasFactory;
    use ScopeBySchool;
    use HasUuid; // ensure UUID is always generated on create, consistent with other models

    // Permet l'assignation en masse des champs principaux
    protected $fillable = [
        'name',
        'code',
        'school_id',
    ];

    // No custom boot needed; HasUuid handles UUID assignment during creation

    public function classrooms(): self|HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Retourne l'école de la filière (relation directe)
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Retourne les cycles de la filière
     */
    public function cycles()
    {
        return $this->hasMany(Cycle::class, 'filiaire_id');
    }
}
