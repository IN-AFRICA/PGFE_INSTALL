<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;

final class VisitRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'exists:classrooms,id'],
            // 'school_id' is only for admin, otherwise prohibited (handled by SchoolIdRule)
            'school_id' => $this->schoolIdRule('exists:schools,id'),
            'subject' => ['required', 'string', 'max:255'],
            'cot_doc_prof' => ['required', 'numeric'],
            'cot_doc_eleve' => ['nullable', 'integer'],
            'cot_meth_proc' => ['required', 'numeric'],
            'cot_matiere' => ['required', 'numeric'],
            'cot_march_lecon' => ['required', 'numeric'],
            'cot_enseignant' => ['required', 'numeric'],
            'cot_eleve' => ['required', 'numeric'],
            'visit_hour' => ['required', 'date'],
            'datefin' => ['nullable', 'date', 'after_or_equal:visit_hour'],
            'summary' => ['required', 'string'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'visiteur' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // classroom_id
            'classroom_id.required' => 'La classe est requise',
            'classroom_id.exists' => "La classe sélectionnée n'existe pas",

            // school_id
            'school_id.required' => "L'école est requise",
            'school_id.exists' => "L'école sélectionnée n'existe pas",

            // subject
            'subject.required' => 'Le sujet est requis',
            'subject.string' => 'Le sujet doit être une chaîne de caractères',
            'subject.max' => 'Le sujet ne doit pas dépasser 255 caractères',

            // Notes (cot_*)
            'cot_doc_prof.required' => 'La note du document prof est requise',
            'cot_doc_prof.numeric' => 'La note du document prof doit être un nombre',

            'cot_doc_eleve.integer' => 'Le contrôle du document de l\'élève doit être un entier',

            'cot_meth_proc.required' => 'La note de la méthode/procédure est requise',
            'cot_meth_proc.numeric' => 'La note de la méthode/procédure doit être un nombre',

            'cot_matiere.required' => 'La note de la matière est requise',
            'cot_matiere.numeric' => 'La note de la matière doit être un nombre',

            'cot_march_lecon.required' => 'La note de la marche de la leçon est requise',
            'cot_march_lecon.numeric' => 'La note de la marche de la leçon doit être un nombre',

            'cot_enseignant.required' => "La note de l'enseignant est requise",
            'cot_enseignant.numeric' => "La note de l'enseignant doit être un nombre",

            'cot_eleve.required' => "La note de l'élève est requise",
            'cot_eleve.numeric' => "La note de l'élève doit être un nombre",

            // visit_hour
            'visit_hour.required' => "L'heure de visite est requise",
            'visit_hour.date' => "L'heure de visite doit être une date valide",

            // datefin
            'datefin.date' => 'La date de fin doit être une date valide',
            'datefin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de visite',

            // summary
            'summary.required' => 'Le résumé est requis',
            'summary.string' => 'Le résumé doit être une chaîne de caractères',

            // fonction_id
            'fonction_id.required' => 'La fonction est requise',
            'fonction_id.exists' => 'La fonction sélectionnée n\'existe pas',

            // visiteur
            'visiteur.required' => 'Le visiteur est requis',
            'visiteur.string' => 'Le visiteur doit être une chaîne de caractères',
        ];
    }
}
