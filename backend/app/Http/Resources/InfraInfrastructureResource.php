<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InfraInfrastructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'date_construction' => $this->date_construction,
            'montant_construction' => $this->montant_construction,
            'emplacement' => $this->emplacement,
            'infra_categorie_id' => $this->infra_categorie_id,
            'infra_bailleur_id' => $this->infra_bailleur_id,
            'school_id' => $this->school_id,
        ];
    }
}
