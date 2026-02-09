<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraEquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'code' => 'string|max:255|unique:infra_equipements,code',
            'date_acquisition' => 'string|max:255',
            'montant_acquisition' => 'numeric',
            'infra_bailleur_id' => 'integer',
            'infra_categorie_id' => 'integer',
            'emplacement' => 'string|max:255',
            'school_id' => 'integer',
        ];
    }

    public function message(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'code.string' => 'The code must be a string.',
            'code.max' => 'The code may not be greater than 255 characters.',
            'code.unique' => 'The code has already been taken.',
            'date_acquisition.string' => 'The date of acquisition must be a string.',
            'date_acquisition.max' => 'The date of acquisition may not be greater than 255 characters.',
            'montant_acquisition.numeric' => 'The acquisition amount must be a number.',
            'infra_bailleur_id.integer' => 'The infra bailleur ID must be an integer.',
            'infra_categorie_id.integer' => 'The infra categorie ID must be an integer.',
            'emplacement.string' => 'The location must be a string.',
            'emplacement.max' => 'The location may not be greater than 255 characters.',
            'school_id.integer' => 'The school ID must be an integer.',
        ];
    }
}
