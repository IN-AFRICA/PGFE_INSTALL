<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;

final class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new Unique('countries', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du pays est requis',
            'name.string' => 'Le nom du pays doit être une chaîne de caractères',
            'name.max' => 'Le nom du pays ne doit pas dépasser 255 caractères',
            'name.unique' => 'Ce pays existe déjà',
        ];
    }
}
