<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonListeSalariesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personal_id' => 'required|integer|exists:personals,id',
            'person_salaire_id' => 'required|integer|exists:person_salaires,id',
            'school_id' => 'required|integer|exists:schools,id',
            'author_id' => 'required|integer|exists:personals,id',
        ];
    }

    public function messages()
    {
        return [
            'personal_id.required' => 'Le champ personnel est obligatoire.',
            'personal_id.integer' => 'Le champ personnel doit être un entier.',
            'personal_id.exists' => 'Le personnel sélectionné est invalide.',
            'person_salaire_id.required' => 'Le champ salaire est obligatoire.',
            'person_salaire_id.integer' => 'Le champ salaire doit être un entier.',
            'person_salaire_id.exists' => 'Le salaire sélectionné est invalide.',
            'school_id.required' => 'Le champ école est obligatoire.',
            'school_id.integer' => 'Le champ école doit être un entier.',
            'school_id.exists' => "L'école sélectionnée est invalide.",
        ];
    }
}
