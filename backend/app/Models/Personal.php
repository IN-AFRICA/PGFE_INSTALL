<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\AutoAssignsSchoolContext;
use App\Models\Concerns\ScopeBySchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Personal extends Model
{
    use AutoAssignsSchoolContext;
    use HasFactory; // auto-attribution school_id
    use ScopeBySchool; // filtrage multi-école

    protected $fillable = [
        'matricule',
        'name',
        'post_name',
        'pre_name',
        'gender',
        'civil_status',
        'country_id',
        'province_id',
        'territory_id',
        'commune_id',
        'school_id',
        'type_id',
        'physical_address',
        'birth_date',
        'birth_place',
        'identity_card_number',
        'father_id',
        'mother_id',
        'academic_level_id',
        'phone',
        'email',
        'fonction_id',
        'mechanisation_id',
        'created_at',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function father()
    {
        return $this->belongsTo(Parents::class, 'father_id');
    }

    public function mother()
    {
        return $this->belongsTo(Parents::class, 'mother_id');
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function fonction()
    {
        return $this->belongsTo(Fonction::class);
    }

    public function mechanisation()
    {
        return $this->belongsTo(Mechanisation::class);
    }

    protected static function booted(): void
    {
        // Hook remplacé par AutoAssignsSchoolContext
    }
}
