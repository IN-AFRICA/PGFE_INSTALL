<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AmmortissementComptabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'justification' => 'string|max:255',
            'date_ammortissement' => 'date',
            'amount' => 'numeric',
            'immo_account_id' => 'integer',
            'immo_sub_account_id' => 'integer',
            'annalytique_comptability_id' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'justification.string' => 'La justification doit être une chaîne de caractères',
            'justification.max' => 'La justification ne doit pas dépasser 255 caractères',
            'date_ammortissement.date' => "La date d'amortissement doit être une date valide",
            'amount.numeric' => 'Le montant doit être un nombre',
            'immo_account_id.integer' => "L'identifiant du compte immo doit être un entier",
            'immo_sub_account_id.integer' => "L'identifiant du sous-compte immo doit être un entier",
            'annalytique_comptability_id.integer' => "L'identifiant de la comptabilité analytique doit être un entier",
        ];
    }
}
