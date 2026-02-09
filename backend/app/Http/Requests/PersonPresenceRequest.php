<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class PersonPresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise les clés d'entrée avant validation pour accepter plusieurs alias
     * (ex: personal_id/personnel_id, camelCase, etc.).
     */
    protected function prepareForValidation(): void
    {
        // Récupérer l'utilisateur connecté (peut être null si non authentifié)
        $user = Auth::user();

        // Normalisation des alias et préremplissage depuis l'utilisateur si absent
        $schoolId = $this->input('school_id', $this->input('schoolId'));
        if ($schoolId === null && $user) {
            $schoolId = data_get($user, 'school_id');
        }

        $this->merge([
            // Autoriser personal_id ou personnelId comme alias de personnel_id
            'personnel_id' => $this->input('personnel_id', $this->input('personal_id', $this->input('personnelId', $this->input('personalId')))),
            // Alias pour presence
            'presence' => $this->input('presence', $this->input('isPresent', $this->input('present'))),
            // Alias pour school_id et author_id (snake et camel) + valeurs par défaut depuis Auth
            'school_id' => $schoolId,
            'author_id' => $this->input('author_id', $this->input('authorId')),
        ]);
    }

    public function rules(): array
    {
        // Règles communes
        $rules = [
            'presence' => ['sometimes', 'boolean'],
            'absent_justified' => ['sometimes', 'boolean'],
            'sick' => ['sometimes', 'boolean'],
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
        ];

        // Création (store): on gère initialize_all et on exige personnel_id si pas initialize_all
        if ($this->isMethod('post')) {
            $rules['initialize_all'] = 'sometimes|boolean';
            $rules['personnel_id'] = ['required_without:initialize_all', 'integer', 'exists:academic_personals,id'];

            return $rules;
        }

        // Mise à jour (PUT/PATCH): personnel_id optionnel, jamais obligatoire
        $rules['personnel_id'] = ['sometimes', 'integer', 'exists:academic_personals,id'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'personnel_id.required' => 'Le champ personnel est obligatoire (personal_id/personnel_id).',
            'personnel_id.integer' => 'Le champ personnel doit être un entier valide.',
            'personnel_id.exists' => 'Le personnel sélectionné est introuvable.',
            'presence.required' => 'Le champ présence est obligatoire.',
            'presence.boolean' => 'Le champ présence doit être un booléen (true/false ou 1/0).',
            'school_id.required' => "Le champ école est obligatoire.",
            'school_id.integer' => "Le champ école doit être un entier.",
            'school_id.exists' => "L'école sélectionnée est introuvable.",
        ];
    }
}
