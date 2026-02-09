<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PersonSalaireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('academicPersonal');
        $data = [
            'id' => $this->id,
            'mois_id' => $this->mois_id,
            'montant' => $this->montant,
            'school_year_id' => $this->school_year_id,
            'description' => $this->description,
            'author_id' => $this->author_id,
            'academic_personal_id' => $this->academic_personal_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        if ($this->academicPersonal) {
            $data['academic_personal'] = [
                'id' => $this->academicPersonal->id,
                'name' => $this->academicPersonal->name,
                'post_name' => $this->academicPersonal->post_name,
                'pre_name' => $this->academicPersonal->pre_name,
                'email' => $this->academicPersonal->email,
                'phone' => $this->academicPersonal->phone,
            ];
        }
        return $data;
    }
}
