<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImmoAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'code' => 'string|max:255|unique:immo_accounts,code',
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
        ];
    }
}
