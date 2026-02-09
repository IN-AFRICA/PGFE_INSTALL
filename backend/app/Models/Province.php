<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Province extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }

    public function territories(): self|HasMany
    {
        return $this->hasMany(Territory::class);
    }

    public function schools(): self|HasMany
    {
        return $this->hasMany(School::class);
    }

    public function personals(): self|HasMany
    {
        return $this->hasMany(AcademicPersonal::class);
    }

    public function students(): self|HasMany
    {
        return $this->hasMany(Student::class);
    }
}
