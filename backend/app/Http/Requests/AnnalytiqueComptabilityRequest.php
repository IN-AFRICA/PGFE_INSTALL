<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AnnalytiqueComptabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'code' => 'string|max:255|unique:annalytique_comptabilities,code',

        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères',
            'code.string' => 'Le code doit être une chaîne de caractères',
            'code.max' => 'Le code ne doit pas dépasser 255 caractères',
            'code.unique' => 'Le code doit être unique',

        ];
    }
}
