<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;

final class PresenceRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'presence' => ['required', 'boolean'],
            'school_id' => $this->schoolIdRule('required|exists:schools,id'),
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
        ];
    }

    public function messages(): array
    {
        return [
            // student_id
            'student_id.required' => "L'étudiant est requis",
            'student_id.exists' => "L'étudiant sélectionné n'existe pas",

            // presence
            'presence.required' => 'La présence est requise',
            'presence.boolean' => 'La présence doit être un booléen',

            // school_id
            'school_id.required' => "L'école est requise",
            'school_id.exists' => "L'école sélectionnée n'existe pas",

            // classroom_id
            'classroom_id.required' => 'La classe est requise',
            'classroom_id.exists' => "La classe sélectionnée n'existe pas",

            // academic_level_id
            'academic_level_id.required' => 'Le niveau académique est requis',
            'academic_level_id.exists' => 'Le niveau académique sélectionné n\'existe pas',
        ];
    }
}
