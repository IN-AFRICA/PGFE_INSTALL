<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraBailleurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:infra_bailleurs,name,' . ($this->route('bailleur')?->id ?? 'NULL') . ',id',
            'description' => 'nullable|string',
            'school_id' => 'nullable|integer',
            'academic_personal_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'school_id.integer' => 'The school ID must be an integer.',
            'academic_personal_id.integer' => 'The academic personal ID must be an integer.',
        ];
    }
}
