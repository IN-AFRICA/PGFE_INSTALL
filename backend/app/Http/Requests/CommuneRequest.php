<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CommuneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:communes,name,'.$this->commune?->id],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la commune est requis',
            'name.unique' => 'Ce nom de commune existe déjà',
            'province_id.exists' => 'La province sélectionnée n\'existe pas',
        ];
    }
}
