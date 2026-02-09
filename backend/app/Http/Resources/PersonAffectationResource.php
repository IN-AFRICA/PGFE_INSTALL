<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PersonAffectationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $academicPersonal = $this->whenLoaded('academicPersonal');
        $schoolYear = $this->whenLoaded('schoolYear');

        $personalName = null;
        if ($academicPersonal) {
            $parts = array_filter([
                $academicPersonal->name ?? null,
                $academicPersonal->post_name ?? null,
                $academicPersonal->pre_name ?? null,
            ]);
            $personalName = $parts ? implode(' ', $parts) : null;
        }

        return [
            'id' => $this->id,
            'lieu_affectation' => $this->lieu_affectation,
            'durree_jours' => $this->durree_jours,
            'description' => $this->description,
            'date_debut' => $this->date_debut,
            'school_year_id' => $this->school_year_id,
            'school_year' => $schoolYear ? [
                'id' => $schoolYear->id,
                'name' => $schoolYear->name ?? null,
            ] : null,
            'school_id' => $this->school_id,
            'academic_personal_id' => $this->academic_personal_id,
            'academic_personal' => $academicPersonal ? [
                'id' => $academicPersonal->id,
                'name' => $personalName,
            ] : null,
            'author_id' => $this->author_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
