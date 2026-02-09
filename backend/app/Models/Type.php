<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SchoolTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

final class Type extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Mutateur pour s'assurer que le titre est un type d'école valide.
     */
    public function setTitleAttribute(string $value): void
    {
        $normalized = ucfirst(mb_strtolower($value)); // Normalisation simple
        if (! in_array($normalized, SchoolTypeEnum::values(), true)) {
            throw new InvalidArgumentException("Type d'école invalide: {$value}. Valeurs autorisées: ".implode(', ', SchoolTypeEnum::values()));
        }
        $this->attributes['title'] = $normalized;
    }

    /**
     * Vérifie si ce type est FORMEL.
     */
    public function isFormel(): bool
    {
        return $this->title === SchoolTypeEnum::FORMEL->value;
    }

    /**
     * Vérifie si ce type est NON FORMEL.
     */
    public function isNonFormel(): bool
    {
        return $this->title === SchoolTypeEnum::NON_FORMEL->value;
    }

    public function personals(): self|HasMany
    {
        return $this->hasMany(AcademicPersonal::class);
    }

    public function students(): self|HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function documents(): self|HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function disciplines(): self|HasMany
    {
        return $this->hasMany(Discipline::class);
    }

    public function registrations(): self|HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
