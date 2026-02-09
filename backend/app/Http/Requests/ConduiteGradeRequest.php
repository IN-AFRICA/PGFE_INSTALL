<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConduiteGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_year_id' => 'required|integer|exists:school_years,id',
            'filiere_id' => 'nullable|integer|exists:filiaires,id',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'student_id' => 'required|integer|exists:students,id',
            'fault_count' => 'required|integer|min:0',
            'conduite_semester_1_id' => 'nullable|integer|exists:conduite_semesters,id',
            'conduite_semester_2_id' => 'nullable|integer|exists:conduite_semesters,id',
        ];
    }

    public function messages(): array
    {
        return [
            'school_year_id.required' => "L'année scolaire est requise",
            'classroom_id.required' => 'La classe est requise',
            'student_id.required' => "L'élève est requis",
            'fault_count.required' => 'Le nombre de fautes est requis',
            'fault_count.integer' => 'Le nombre de fautes doit être un entier',
            'fault_count.min' => 'Le nombre de fautes doit être au moins 0',
        ];
    }
}
