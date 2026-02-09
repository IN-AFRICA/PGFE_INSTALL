<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CivilStatusEnum;
use App\Enums\GenderEnum;
use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

final class Student extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory; // auto school_id
    use ScopeBySchool; // filtrage multi-école
    use SoftDeletes; // activation des soft deletes pour gérer forceDeleted
    use \App\Models\Concerns\HasUuid;

    protected $fillable = [
        'name',
        'lastname',
        'firstname',
        'gender',
        'birth_date',
        'birth_place',
        'civil_status',
        'address',
        'phone_number',
        'email',
        'image', // chemin de l'image (nullable)
        'province_id',
        'territory_id',
        'commune_id',
        'parents_id',
        'parent2_id',
        'parent3_id',
        'country_id',
        'matricule',
        'school_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'gender' => GenderEnum::class,
        'civil_status' => CivilStatusEnum::class,
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'territory_id');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function parents(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parents_id');
    }

    public function parent2(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent2_id');
    }

    public function parent3(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent3_id');
    }

    public function documents(): self|HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function registration(): self|HasOne
    {
        return $this->hasOne(Registration::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // Relations académiques utilisées par le bulletin
    public function notes(): HasMany
    {
        return $this->hasMany(FicheCotation::class, 'student_id');
    }

    public function conduiteGrades(): HasMany
    {
        return $this->hasMany(ConduiteGrade::class, 'student_id');
    }

    public function deliberations(): HasMany
    {
        return $this->hasMany(Deliberation::class, 'student_id');
    }

    public function repechages(): HasMany
    {
        return $this->hasMany(Repechage::class, 'student_id');
    }

    /**
     * URL publique du fichier image (storage public) ou null si absent.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    protected static function booted(): void
    {
        self::deleted(function (self $student) {
            // Supprimer l'image uniquement en suppression définitive
            if (method_exists($student, 'isForceDeleting') && $student->isForceDeleting()) {
                if (! empty($student->image) && Storage::disk('public')->exists($student->image)) {
                    Storage::disk('public')->delete($student->image);
                }
            }
        });
    }
}
