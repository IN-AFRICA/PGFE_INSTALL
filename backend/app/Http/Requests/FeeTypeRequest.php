<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Supporte store et update: détecter l'ID à ignorer pour la contrainte unique
        $routeModel = $this->route('feeType') ?? $this->route('type') ?? null; // {type} avec apiResource('types', ...)
        $currentId = is_object($routeModel) ? ($routeModel->id ?? null) : (is_numeric($routeModel) ? (int) $routeModel : null);

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fee_types', 'code')->ignore($currentId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du type de frais (name) est requis.',
            'name.string' => 'Le nom du type de frais doit être une chaîne de caractères.',
            'name.max' => 'Le nom du type de frais ne peut pas dépasser 255 caractères.',

            'code.required' => 'Le code du type de frais (code) est requis.',
            'code.string' => 'Le code du type de frais doit être une chaîne de caractères.',
            'code.max' => 'Le code du type de frais ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code de type de frais existe déjà.',

            'description.string' => 'La description doit être une chaîne de caractères.',
        ];
    }
}
