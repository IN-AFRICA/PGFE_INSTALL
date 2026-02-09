<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AbandonCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_year_id' => 'required|integer|exists:school_years,id',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'filiere_id' => 'required|integer|exists:filiaires,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'student_id' => 'required|integer|exists:students,id',
            'comment' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'school_year_id.required' => 'L\'année scolaire est requise.',
            'school_year_id.integer' => 'L\'année scolaire doit être un entier.',
            'classroom_id.required' => 'La classe est requise.',
            'filiere_id.integer' => 'La filière doit être un entier.',
            'filiere_id.exists' => 'La filière sélectionnée est invalide.',
            'semester_id.required' => 'Le semestre est requis.',
            'semester_id.required' => 'Le semestre est requis.',
            'semester_id.integer' => 'Le semestre doit être un entier.',
            'semester_id.exists' => 'Le semestre sélectionné est invalide.',
            'student_id.required' => 'L\'étudiant est requis.',
            'student_id.exists' => 'L\'étudiant sélectionné est invalide.',
            'comment.string' => 'Le commentaire doit être une chaîne de caractères.',
        ];
    }
}
