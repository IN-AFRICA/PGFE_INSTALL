<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

final class School extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function classrooms(): HasMany|self
    {
        return $this->hasMany(Classroom::class);
    }

    public function personals(): HasMany|self
    {
        return $this->hasMany(AcademicPersonal::class);
    }

    public function visits(): HasMany|self
    {
        return $this->hasMany(Visit::class);
    }

    public function students(): HasMany|self
    {
        return $this->hasMany(Student::class);
    }

    public function disciplines(): HasMany|self
    {
        return $this->hasMany(Discipline::class);
    }

    public function registration(): HasOne|self
    {
        return $this->hasOne(Registration::class);
    }

    public function accountTypes()
    {
        return $this->hasMany(AccountType::class);
    }

    public function inputAccounts()
    {
        return $this->hasMany(InputAccount::class);
    }

    public function outputAccounts()
    {
        return $this->hasMany(OutputAccount::class);
    }

    public function cycles(): HasMany|self
    {
        return $this->hasMany(Cycle::class);
    }

    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
        /**
     * Retourne toutes les filières de l'école (relation directe)
     */
    public function filiaires()
    {
        return $this->hasMany(Filiaire::class, 'school_id');
    }
}
