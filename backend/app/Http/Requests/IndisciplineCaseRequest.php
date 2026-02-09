<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IndisciplineCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'student_id' => 'required|integer|exists:students,id',
            'fault_count' => 'required|integer|min:1',
            'action' => 'required|string',
            'roi' => 'nullable|string',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'filiere_id' => 'nullable|integer|exists:filiaires,id',
            'school_year_id' => 'required|integer|exists:school_years,id',
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'La date est obligatoire',
            'date.date' => 'La date doit être une date valide',
            'student_id.required' => "L'identifiant de l'étudiant est obligatoire",
            'student_id.integer' => "L'identifiant de l'étudiant doit être un entier",
            'student_id.exists' => "L'étudiant spécifié n'existe pas",
            'fault_count.required' => 'Le nombre de fautes est obligatoire',
            'fault_count.integer' => 'Le nombre de fautes doit être un entier',
            'fault_count.min' => 'Le nombre de fautes doit être au moins 1',
            'action.required' => "L'action est obligatoire",
            'action.string' => "L'action doit être une chaîne de caractères",
            'action.max' => "L'action ne doit pas dépasser 255 caractères",
            'classroom_id.required' => "L'identifiant de la classe est obligatoire",
            'classroom_id.integer' => "L'identifiant de la classe doit être un entier",
            'classroom_id.exists' => "La classe spécifiée n'existe pas",
            'filiere_id.integer' => "L'identifiant de la filière doit être un entier",
            'filiere_id.exists' => "La filière spécifiée n'existe pas",
            'school_year_id.required' => "L'identifiant de l'année scolaire est obligatoire",
            'school_year_id.integer' => "L'identifiant de l'année scolaire doit être un entier",
            'school_year_id.exists' => "L'année scolaire spécifiée n'existe pas",
        ];
    }
}
