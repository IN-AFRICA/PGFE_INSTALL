<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonCongeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_personal_id' => 'required|integer|exists:academic_personals,id',
            'jour_demand' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'creer_a' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'academic_personal_id.required' => 'Le champ personnel académique est obligatoire.',
            'academic_personal_id.integer' => 'Le champ personnel académique doit être un entier.',
            'academic_personal_id.exists' => 'Le personnel académique sélectionné est invalide.',
            'jour_demand.required' => 'Le champ jour demandé est obligatoire.',
            'jour_demand.integer' => 'Le champ jour demandé doit être un entier.',
            'jour_demand.min' => 'Le champ jour demandé doit être au moins 1.',
            'description.string' => 'Le champ description doit être une chaîne de caractères.',
            'school_id.required' => 'Le champ école est obligatoire.',
            'school_id.integer' => 'Le champ école doit être un entier.',
            'school_id.exists' => "L'école sélectionnée est invalide.",
            'author_id.required' => 'Le champ auteur est obligatoire.',
            'author_id.integer' => 'Le champ auteur doit être un entier.',
            'author_id.exists' => "L'auteur sélectionné est invalide.",
            'creer_a.required' => 'Le champ date de création est obligatoire.',
            'creer_a.date' => 'Le champ date de création doit être une date valide.',
        ];
    }
}
