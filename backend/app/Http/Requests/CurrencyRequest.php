<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string',
            'symbol' => 'nullable|string',
            'is_default' => 'boolean',
        ];

    }

    public function messages()
    {
        return [
            'code' => 'Le code de la devise doit comporter exactement 3 caractères et être unique.',
            'code.unique' => 'Le code de la devise doit être unique.',
            'code.exits' => 'Le code de la devise  existe deja.',
            'name' => 'Le nom de la devise est requis.',
        ];
    }
}
