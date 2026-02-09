<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AcademicLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_levels', 'name')->ignore($this->academic_level),
            ],
            'cycle_id' => ['required', 'exists:cycles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du niveau académique est requis',
            'name.unique' => 'Ce niveau académique existe déjà',
            'cycle_id.required' => 'Le cycle est requis',
            'cycle_id.exists' => 'Le cycle sélectionné n\'existe pas',
        ];
    }
}
