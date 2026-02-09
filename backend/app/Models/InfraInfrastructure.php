<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InfraInfrastructure extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'name',
        'code',
        'date_construction',
        'montant_construction',
        'infra_categorie_id',
        'infra_bailleur_id',
        'description',
        'school_id',
        'emplacement',
    ];

    public function categorie(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InfraCategorie::class, 'infra_categorie_id');
    }

    public function bailleur(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InfraBailleur::class, 'infra_bailleur_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function inventaires(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InfraInfrastructureInventaire::class, 'infra_infrastructure_id');
    }

    public function etats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InfraEtat::class, 'infra_infrastructure_id');
    }
}
