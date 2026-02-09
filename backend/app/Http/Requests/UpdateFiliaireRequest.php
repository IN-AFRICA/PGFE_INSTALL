<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFiliaireRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $filiaireId = $this->route('filiaire')?->id;
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('filiaires', 'name')->ignore($filiaireId),
            ],
            'code' => [
                'sometimes',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la filière est requis',
            'name.unique' => 'Cette filière existe déjà.',
        ];
    }
}
