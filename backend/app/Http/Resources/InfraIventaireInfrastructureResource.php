<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InfraIventaireInfrastructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'infra_infrastructure_id' => $this->infra_infrastructure_id,
            'description' => $this->description,
            'school_id' => $this->school_id,
            'academic_personal_id' => $this->academic_personal_id,
        ];
    }
}
