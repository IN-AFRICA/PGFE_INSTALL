<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TerritoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:territories,name,'.$this->territory?->id],
            'province_id' => ['nullable', 'exists:provinces,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du territoire est requis',
            'name.unique' => 'Ce nom de territoire existe déjà',
            'province_id.exists' => 'La province sélectionnée n\'existe pas',
        ];
    }
}
