<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AccountTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_number_id' => ['required', 'exists:account_numbers,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'academic_personal_id' => ['required', 'exists:academic_personals,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number_id.required' => 'Le numéro de compte est obligatoire.',
            'account_number_id.exists' => 'Le numéro de compte sélectionné est invalide.',

            'school_id.required' => 'L’école est obligatoire.',
            'school_id.exists' => 'L’école sélectionnée est invalide.',

            'academic_personal_id.required' => 'La personne académique est obligatoire.',
            'academic_personal_id.exists' => 'La personne académique sélectionnée est invalide.',

            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        ];
    }

}
