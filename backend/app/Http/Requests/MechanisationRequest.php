<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MechanisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est requis',
        ];
    }
}
