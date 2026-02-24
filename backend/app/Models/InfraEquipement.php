<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class InfraEquipement extends Model
{
    use \App\Models\Concerns\AutoAssignsSchoolContext;
    use \App\Models\Concerns\ScopeBySchool;
    use \App\Models\Concerns\HasUuid;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = [
        'name',
        'code',
        'date_acquisition',
        'montant_acquisition',
        'infra_bailleur_id',
        'infra_categorie_id',
        'emplacement',
        'school_id',
    ];

    public function categorie()
    {
        return $this->belongsTo(InfraCategorie::class, 'infra_categorie_id');
    }

    public function bailleur()
    {
        return $this->belongsTo(InfraBailleur::class, 'infra_bailleur_id');
    }
}
