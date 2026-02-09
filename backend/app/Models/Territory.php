<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Territory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
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
