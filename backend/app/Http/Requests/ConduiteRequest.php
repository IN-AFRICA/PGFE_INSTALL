<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConduiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est requis',
            'school_id.required' => "L'école est requise",
        ];
    }
}
