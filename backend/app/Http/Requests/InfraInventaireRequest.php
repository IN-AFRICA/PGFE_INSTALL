<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraInventaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'infra_equipement_id' => 'required|integer|exists:infra_equipements,id',
            'observation' => 'nullable|string',
            'school_id' => 'nullable|integer|exists:schools,id',
            'author_id' => 'nullable|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'infra_equipement_id.required' => 'L\'équipement d\'infrastructure est obligatoire.',
            'infra_equipement_id.integer' => 'L\'identifiant de l\'équipement d\'infrastructure doit être un entier.',
            'infra_equipement_id.exists' => 'L\'équipement d\'infrastructure sélectionné est invalide.',
            'observation.string' => 'L\'observation doit être une chaîne de caractères.',
            'school_id.integer' => 'L\'identifiant de l\'école doit être un entier.',
            'school_id.exists' => 'L\'école sélectionnée est invalide.',
            'author_id.integer' => 'L\'identifiant de l\'auteur doit être un entier.',
            'author_id.exists' => 'L\'auteur sélectionné est invalide.',
        ];
    }
}
