<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StudentRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'classroom_id' => $this->classroom_id,
            'academic_level_id' => $this->academic_level_id,
            'filiaire_id' => $this->filiaire_id,
            'cycle_id' => $this->cycle_id,

            // Infos dérivées existantes
            'gender' => $this->student?->gender,
            'date_of_birth' => $this->student?->birth_date,
            'email' => $this->student?->email,
            'address' => $this->student?->address,
            'matricule' => $this->student?->matricule,
            'student_name' => $this->student_name,
            'classroom_name' => $this->classroom_name,
            'academic_level_name' => $this->academic_level_name,
            'filiaire_name' => $this->whenLoaded('filiaire', fn () => $this->filiaire?->name),
            'cycle_name' => $this->whenLoaded('cycle', fn () => $this->cycle?->name),
            'student_image' => $this->student?->image,

            // Objets relationnels complets (affichage)
            'student' => $this->whenLoaded('student', fn () => $this->student),
            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom),
            'academic_level' => $this->whenLoaded('academicLevel', fn () => $this->academicLevel),
            'filiaire' => $this->whenLoaded('filiaire', fn () => $this->filiaire),
            'cycle' => $this->whenLoaded('cycle', fn () => $this->cycle),

            // Nouveau: parents liés à l'inscription
            'registration_parents' => [
                'parent1_id' => $this->registrationParents?->parent1_id,
                'parent2_id' => $this->registrationParents?->parent2_id,
                'parent3_id' => $this->registrationParents?->parent3_id,
                'parent1' => $this->whenLoaded('registrationParents', fn () => $this->registrationParents?->parent1),
                'parent2' => $this->whenLoaded('registrationParents', fn () => $this->registrationParents?->parent2),
                'parent3' => $this->whenLoaded('registrationParents', fn () => $this->registrationParents?->parent3),
            ],
        ];
    }
}
