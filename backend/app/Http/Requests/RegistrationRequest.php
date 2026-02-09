<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;

final class RegistrationRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['prohibited'],
            'classroom_id' => ['sometimes', 'exists:classrooms,id'],
            'filiaire_id' => ['required', 'exists:filiaires,id'],
            'academic_personal_id' => ['required', 'exists:academic_personals,id'],
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            'cycle_id' => ['required', 'exists:cycles,id'],
            'note' => ['nullable', 'string'],
            // Nouveaux champs parents
            'parent1_id' => ['required', 'integer', 'exists:parents,id'],
            'parent2_id' => ['nullable', 'integer', 'exists:parents,id'],
            'parent3_id' => ['nullable', 'integer', 'exists:parents,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // Règles de cohérence pour les parents: éviter les doublons
            $p1 = $this->input('parent1_id');
            $p2 = $this->input('parent2_id');
            $p3 = $this->input('parent3_id');
            if ($p1 && $p2 && (int) $p1 === (int) $p2) {
                $validator->errors()->add('parent2_id', 'Le parent 2 ne peut pas être identique au parent 1.');
            }
            if ($p1 && $p3 && (int) $p1 === (int) $p3) {
                $validator->errors()->add('parent3_id', 'Le parent 3 ne peut pas être identique au parent 1.');
            }
            if ($p2 && $p3 && (int) $p2 === (int) $p3) {
                $validator->errors()->add('parent3_id', 'Le parent 3 ne peut pas être identique au parent 2.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'classroom_id.exists' => 'La classe spécifiée est introuvable.',
            'filiaire_id.required' => 'La filière (filiaire_id) est requise.',
            'filiaire_id.exists' => 'La filière indiquée est introuvable.',
            'academic_personal_id.required' => 'Le personnel académique (academic_personal_id) est requis.',
            'academic_personal_id.exists' => 'Le personnel académique indiqué est introuvable.',
            'academic_level_id.required' => 'Le niveau académique (academic_level_id) est requis.',
            'academic_level_id.exists' => 'Le niveau académique indiqué est introuvable.',
            'cycle_id.required' => 'Le cycle (cycle_id) est requis.',
            'cycle_id.exists' => 'Le cycle indiqué est introuvable.',
            'note.string' => 'La note doit être une chaîne de caractères.',
        ];
    }
}
