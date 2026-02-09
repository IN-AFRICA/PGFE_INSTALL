<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ANewComptabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_plan_id' => 'required|exists:account_plan,id',
            'sub_account_plan_id' => 'required|exists:sub_account_plan,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:input,output',
            'justification' => 'required|string|max:255',

        ];
    }

    public function messages(): array
    {
        return [
            'account_plan_id.required' => 'Le plan de compte est obligatoire.',
            'account_plan_id.exists' => 'Le plan de compte sélectionné est invalide.',
            'sub_account_plan_id.required' => 'Le sous-compte est obligatoire.',
            'sub_account_plan_id.exists' => 'Le sous-compte sélectionné est invalide.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être positif.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être "input" ou "output".',
            'justification.required' => 'La justification est obligatoire.',
            'justification.string' => 'La justification doit être une chaîne de caractères.',
            'justification.max' => 'La justification ne doit pas dépasser 255 caractères.',
        ];
    }
}
