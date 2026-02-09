<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonSalaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mois_id' => 'required|integer|exists:mois,id',
            'montant' => 'required|numeric|min:0',
            'school_year_id' => 'required|integer|exists:school_years,id',
            'description' => 'nullable|string',
            'author_id' => 'required|integer|exists:academic_personals,id',
            'academic_personal_id' => 'required|integer|exists:academic_personals,id',
        ];
    }

    public function messages()
    {
        return [
            'mois_id.required' => 'Le champ mois est obligatoire.',
            'mois_id.integer' => 'Le champ mois doit être un entier.',
            'mois_id.exists' => 'Le mois sélectionné est invalide.',
            'montant.required' => 'Le champ montant est obligatoire.',
            'montant.numeric' => 'Le champ montant doit être un nombre.',
            'montant.min' => 'Le champ montant doit être au moins 0.',
            'school_year_id.required' => 'Le champ année scolaire est obligatoire.',
            'school_year_id.integer' => 'Le champ année scolaire doit être un entier.',
            'school_year_id.exists' => "L'année scolaire sélectionnée est invalide.",
            'description.string' => 'Le champ description doit être une chaîne de caractères.',
            'academic_personal_id.required' => 'Le champ personnel académique payé est obligatoire.',
            'academic_personal_id.integer' => 'Le champ personnel académique payé doit être un entier.',
            'academic_personal_id.exists' => 'Le personnel académique payé sélectionné est invalide.',
        ];
    }
}
