<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InfraEquipementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'date_acquisition' => $this->date_acquisition,
            'montant_acquisition' => $this->montant_acquisition,
            'infra_bailleur_id' => $this->infra_bailleur_id,
            'infra_categorie_id' => $this->infra_categorie_id,
            'emplacement' => $this->emplacement,
            'school_id' => $this->school_id,
        ];
    }
}
