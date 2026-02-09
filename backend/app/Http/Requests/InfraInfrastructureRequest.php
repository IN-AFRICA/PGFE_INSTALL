<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraInfrastructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'date_construction' => 'string|max:255',
            'montant_construction' => 'integer',
            'emplacement' => 'string|max:255',
            'infra_categorie_id' => 'integer',
            'infra_bailleur_id' => 'integer',
            'school_id' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'name.required' => 'Le nom est requis.',
            'date_construction.string' => 'La date de construction doit être une chaîne de caractères.',
            'date_construction.max' => 'La date de construction ne doit pas dépasser 255 caractères.',
            'date_construction.required' => 'La date de construction est requise.',
            'montant_construction.integer' => 'Le montant de construction doit être un entier.',
            'emplacement.string' => "L'emplacement doit être une chaîne de caractères.",
            'emplacement.max' => "L'emplacement ne doit pas dépasser 255 caractères.",
            'emplacement.required' => "L'emplacement est requis.",
            'infra_categorie_id.integer' => "L'identifiant de la catégorie d'infrastructure doit être un entier.",
            'infra_bailleur_id.integer' => "L'identifiant du bailleur d'infrastructure doit être un entier.",
            'school_id.integer' => "L'identifiant de l'école doit être un entier.",
        ];
    }
}
