<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FormationContinueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'academic_personal_id' => ['required', 'exists:academic_personals,id'],
            'school_id' => ['sometimes', 'exists:schools,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'filiere_id' => ['nullable', 'exists:filiaires,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'start_date.required' => 'La date de début est requise.',
            'start_date.date' => 'La date de début doit être une date valide.',
            'end_date.date' => 'La date de fin doit être une date valide.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'location.string' => 'Le lieu doit être une chaîne de caractères.',
            'location.max' => 'Le lieu ne doit pas dépasser 255 caractères.',
            'academic_personal_id.required' => 'Le formateur (personnel académique) est requis.',
            'academic_personal_id.exists' => 'Le formateur sélectionné n\'existe pas.',
            'school_id.required' => 'L\'école est requise.',
            'school_id.exists' => 'L\'école sélectionnée n\'existe pas.',
            'classroom_id.exists' => 'La classe sélectionnée n\'existe pas.',
            'filiere_id.exists' => 'La filière sélectionnée n\'existe pas.',
        ];
    }
}

