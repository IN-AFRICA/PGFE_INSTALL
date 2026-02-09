<?php

declare(strict_types=1);

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

final class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'name' => $this->full_name ?? $this->name,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'email' => $this->email,
            'address' => $this->address,
            'image' => $this->image,
            // Ajoutez ici d'autres champs pertinents du modèle Student si besoin
        ];
    }
}
