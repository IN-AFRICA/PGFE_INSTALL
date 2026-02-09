<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'critiques' => 'required|string|max:255',
            'c1_quantite_travail' => 'required|numeric|min:0',
            'c2_theorie_pratique' => 'required|numeric|min:0',
            'c3_determ_ress_perso' => 'required|numeric|min:0',
            'c4_ponctualite' => 'required|numeric|min:0',
            'c5_dr_att_posit_collab' => 'required|numeric|min:0',
            'academic_personal_id' => 'required|integer|exists:academic_personals,id',
            'school_year_id' => 'required|exists:school_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'author_id' => 'required|integer|exists:personals,id',
        ];
    }

    public function messages(): array
    {
        return [
            'critiques.required' => 'Les critiques sont obligatoires.',
            'c1_quantite_travail.required' => 'La quantité de travail est obligatoire.',
            'c2_theorie_pratique.required' => 'La théorie et la pratique sont obligatoires.',
            'c3_determ_ress_perso.required' => 'La détermination des ressources personnelles est obligatoire.',
            'c4_ponctualite.required' => 'La ponctualité est obligatoire.',
            'c5_dr_att_posit_collab.required' => "La disponibilité, l'attitude positive et la collaboration sont obligatoires.",
            'academic_personal_id.required' => "L'enseignant évalué est obligatoire.",
            'academic_personal_id.exists' => "L'enseignant évalué est introuvable.",
            'school_year_id.required' => "L'année scolaire est obligatoire.",
            'semester_id.required' => 'Le semestre est obligatoire.',
        ];
    }
}
