<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InfraInfrastructureInventaireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'infra_infrastructure_id' => $this->infra_infrastructure_id,
            'title' => $this->title,
            'description' => $this->description,
            'inventory_date' => optional($this->inventory_date)->toDateString(),
            'status' => $this->status,
            'observations' => $this->observations,
            'school_id' => $this->school_id,
            'author_id' => $this->author_id,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
