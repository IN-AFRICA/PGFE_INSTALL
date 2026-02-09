<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Country extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function personals(): HasMany
    {
        return $this->hasMany(AcademicPersonal::class);
    }

    public function students(): self|HasMany
    {
        return $this->hasMany(Student::class);
    }
}
