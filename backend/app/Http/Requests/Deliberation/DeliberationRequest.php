<?php

declare(strict_types=1);

namespace App\Http\Requests\Deliberation;

use Illuminate\Foundation\Http\FormRequest;

final class DeliberationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Pour la création (POST), on peut exiger student_id.
        // Pour la mise à jour (PUT/PATCH), on ne doit pas l'exiger
        // afin de permettre les updates ciblés sur is_validated.
		$studentRule = $this->isMethod('post')
			? 'required|exists:students,id'
			: 'sometimes|exists:students,id';

        return [
			'student_id' => $studentRule,
            // Les autres champs sont optionnels, ils seront remplis automatiquement
            'classroom_id' => 'nullable|exists:classrooms,id',
            'filiaire_id' => 'nullable|exists:filiaires,id',
            'academic_level_id' => 'nullable|exists:academic_levels,id',
            'cycle_id' => 'nullable|exists:cycles,id',
            'course_id' => 'nullable|exists:courses,id',
            'school_year_id' => 'nullable|exists:school_years,id',
            'is_validated' => 'boolean',
            'conduite_grade_id' => 'nullable|exists:conduite_grades,id',
        ];
    }
}
