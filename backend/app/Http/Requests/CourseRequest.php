<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            'filiaire_id' => ['required', 'exists:filiaires,id'],
            'cycle_id' => ['required', 'exists:cycles,id'],
            // Empêche l’envoi de school_id par le client: il est défini côté serveur
            'school_id' => ['prohibited'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            //            'teacher_id' => ['required', 'exists:academic_personals,id'],
            'hourly_volume' => ['required', 'integer'],
            'max_period_1' => ['required', 'numeric'],
            'max_period_2' => ['required', 'numeric'],
            'max_period_3' => ['required', 'numeric'],
            'max_period_4' => ['required', 'numeric'],
            'max_exam_1' => ['required', 'numeric'],
            'max_exam_2' => ['required', 'numeric'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est requis',
            'level_id.required' => 'Le niveau est requis',
            'level_id.exists' => "Le niveau sélectionné n'existe pas",
            'filiere_id.required' => 'La filière est requise',
            'filiere_id.exists' => "La filière sélectionnée n'existe pas",
            'school_id.prohibited' => 'Le champ école est géré automatiquement par le serveur.',
            'classroom_id.required' => 'La classe est requise',
            'classroom_id.exists' => "La classe sélectionnée n'existe pas",
            'teacher_id.required' => 'Le titulaire est requis',
            'teacher_id.exists' => "Le titulaire sélectionné n'existe pas",
            'hourly_volume.required' => 'Le volume horaire est requis',
            'max_period_1.required' => 'La note max période 1 est requise',
            'max_period_2.required' => 'La note max période 2 est requise',
            'max_period_3.required' => 'La note max période 3 est requise',
            'max_period_4.required' => 'La note max période 4 est requise',
            'max_exam_1.required' => 'La note max examen 1 est requise',
            'max_exam_2.required' => 'La note max examen 2 est requise',
        ];
    }
}
