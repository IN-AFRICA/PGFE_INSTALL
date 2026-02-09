<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'school_id' => 'required|exists:schools,id',
            'author_id' => 'required|exists:academic_personals,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'school_id.required' => 'The school ID field is required.',
            'school_id.exists' => 'The selected school ID is invalid.',
            'author_id.required' => 'The author ID field is required.',
            'author_id.exists' => 'The selected author ID is invalid.',
        ];
    }
}
