<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:provinces,name,'],
            'country_id' => ['nullable', 'exists:countries,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la province est requis.',
            'name.string' => 'Le nom de la province doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la province ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce nom de province existe déjà.',
            'country_id.required' => 'Le pays est requis.',
            'country_id.exists' => 'Le pays sélectionné est introuvable.',
        ];
    }
}
