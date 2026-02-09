<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConduitesGradesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Identifiants directs depuis le modèle
            'school_year_id' => $this->school_year_id,
            'filiere_id' => $this->filiere_id,
            'classroom_id' => $this->classroom_id,
            'student_id' => $this->student_id,

            // Libellés via relations préchargées
            'school_year' => $this->whenLoaded('schoolYear', fn () => $this->schoolYear?->name),
            'filiere' => $this->whenLoaded('filiere', fn () => $this->filiere?->name),
            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom?->name),
            'student' => $this->whenLoaded('student', fn () => $this->student ? mb_trim(($this->student->firstname ?? '').' '.($this->student->name ?? '')) : null),

            'fault_count' => $this->fault_count !== null ? (int) $this->fault_count : null,
            'conduite_semester_1_id' => $this->conduite_semester_1_id !== null ? (int) $this->conduite_semester_1_id : null,
            'conduite_semester_1' => $this->whenLoaded('conduiteSemester1', fn () => optional($this->conduiteSemester1->semester)->name ?? null),
            'conduite_semester_2_id' => $this->conduite_semester_2_id !== null ? (int) $this->conduite_semester_2_id : null,
            'conduite_semester_2' => $this->whenLoaded('conduiteSemester2', fn () => optional($this->conduiteSemester2->semester)->name ?? null),
        ];
    }
}
