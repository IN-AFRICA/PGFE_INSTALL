<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DisciplinaryActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'type_id' => ['required', 'exists:types,id'],
            'author_id' => ['required', 'exists:academic_personals,id'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => "L'apprenant est requis",
            'student_id.exists' => "L'apprenant sélectionné n'existe pas",
            'school_id.required' => "L'école est requise",
            'school_id.exists' => "L'école sélectionnée n'existe pas",
            'type_id.required' => 'Le type est requis',
            'type_id.exists' => "Le type sélectionné n'existe pas",
            'author_id.required' => "L'auteur est requis",
            'author_id.exists' => "L'auteur sélectionné n'existe pas",
        ];
    }
}
