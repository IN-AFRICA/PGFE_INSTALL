<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConduiteSemesterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conduite_id' => $this->conduite_id,
            'conduite' => $this->whenLoaded('conduite', fn () => $this->conduite->label ?? null),
            'school_year_id' => $this->school_year_id,
            'school_year' => $this->whenLoaded('schoolYear', fn () => $this->schoolYear->name ?? null),
            'semester_id' => $this->semester_id,
            'semester' => $this->whenLoaded('semester', fn () => $this->semester->name ?? null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
