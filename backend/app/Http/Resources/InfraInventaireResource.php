<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InfraInventaireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'infra_equipement_id' => $this->infra_equipement_id,
            'observation' => $this->observation,
            'school_id' => $this->school_id,
            'author_id' => $this->author_id,
        ];
    }
}
