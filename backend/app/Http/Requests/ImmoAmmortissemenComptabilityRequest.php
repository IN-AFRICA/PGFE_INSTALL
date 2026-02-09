<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImmoAmmortissemenComptabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'code' => 'string|max:255|unique:immo_ammortissemen_comptabilities,code',
            'model' => 'string|max:255',
            'amount' => 'numeric',
            'purchase_date' => 'date',
            'number_years' => 'integer',
            'immo_account_id' => 'integer',
            'immo_sub_account_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères',
            'code.string' => 'Le code doit être une chaîne de caractères',
            'code.max' => 'Le code ne doit pas dépasser 255 caractères',
            'code.unique' => 'Le code doit être unique',
            'model.string' => 'Le modèle doit être une chaîne de caractères',
            'model.max' => 'Le modèle ne doit pas dépasser 255 caractères',
            'amount.numeric' => 'Le montant doit être un nombre',
            'purchase_date.date' => "La date d'achat doit être une date valide",
            'number_years.integer' => "Le nombre d'années doit être un entier",
            'immo_account_id.integer' => "L'identifiant du compte immo doit être un entier",
            'immo_sub_account_id.integer' => "L'identifiant du sous-compte immo doit être un entier",
        ];
    }
}
