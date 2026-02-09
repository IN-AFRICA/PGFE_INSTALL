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
use Illuminate\Support\Facades\Storage;

final class AcademicPersonal extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory; // auto school_id
    use ScopeBySchool; // filtrage multi-école
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \App\Models\Concerns\HasUuid;

    protected $appends = [
        'image_url',
    ];

    protected $fillable = [
        'user_id',
        'mechanisation_id',
        'parent_id',
        'fonction_id',
        'matricule',
        'name',
        'post_name',
        'pre_name',
        'gender',
        'civil_status',
        'birth_date',
        'birth_place',
        'identity_card_number',
        'phone',
        'email',
        'image',
        'physical_address',
        'country_id',
        'province_id',
        'territory_id',
        'commune_id',
        'school_id',
        'type_id',
        'academic_level_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

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

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'father_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'mother_id');
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(Fonction::class, 'fonction_id');
    }

    // La relation visits via academic_personal_id n'est plus utilisée pour les visites

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function document(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function disciplines(): HasMany|self
    {
        return $this->hasMany(Discipline::class);
    }

    public function registrations(): HasMany|self
    {
        return $this->hasMany(Registration::class);
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

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'academic_personal_course', 'academic_personal_id', 'course_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'academic_personal_id');
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(TeacherUnavailability::class, 'academic_personal_id');
    }

    protected function casts(): array
    {
        return [
            'gender' => GenderEnum::class,
            'civil_status' => CivilStatusEnum::class,
        ];
    }

    /**
     * URL publique de l'image du personnel.
     *
     * Priorité à la colonne locale `image` (comme pour Student),
     * sinon fallback sur le dernier Document lié avec un `path`.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! empty($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        $document = $this->document()
            ->whereNotNull('path')
            ->orderByDesc('id')
            ->first();

        if (! $document || empty($document->path)) {
            return null;
        }

        return Storage::disk('public')->url($document->path);
    }
}
