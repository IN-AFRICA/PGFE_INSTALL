<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_personal_id' => 'required|integer|exists:academic_personals,id',
            'lieu_affectation' => 'required|string|max:255',
            'durree_jours' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'date_debut' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'academic_personal_id.required' => 'Le champ personnel académique est obligatoire.',
            'academic_personal_id.integer' => 'Le champ personnel académique doit être un entier.',
            'academic_personal_id.exists' => 'Le personnel académique sélectionné est invalide.',
            'lieu_affectation.required' => "Le champ lieu d'affectation est obligatoire.",
            'lieu_affectation.string' => "Le champ lieu d'affectation doit être une chaîne de caractères.",
            'lieu_affectation.max' => "Le champ lieu d'affectation ne doit pas dépasser 255 caractères.",
            'durree_jours.required' => 'Le champ durée en jours est obligatoire.',
            'durree_jours.integer' => 'Le champ durée en jours doit être un entier.',
            'durree_jours.min' => 'Le champ durée en jours doit être au moins 1.',
            'description.string' => 'Le champ description doit être une chaîne de caractères.',
            'date_debut.date' => 'La date de début doit être une date valide.',
        ];
    }
}
