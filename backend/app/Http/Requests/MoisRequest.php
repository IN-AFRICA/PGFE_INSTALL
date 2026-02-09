<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MoisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:mois,name,'.($this->mois->id ?? 'NULL').',id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du mois est requis.',
            'name.string' => 'Le nom du mois doit être une chaîne de caractères.',
            'name.unique' => 'Ce nom de mois existe déjà.',
        ];
    }
}
