<?php

declare(strict_types=1);

namespace App\Http\Resources\Deliberation;

use Illuminate\Http\Resources\Json\JsonResource;

final class DeliberationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        // Récupérer la fiche de cotation liée à la délibération
        $fiche = \App\Models\FicheCotation::where('student_id', $this->student_id ?? $this->student?->id)
            ->where('classroom_id', $this->classroom_id ?? $this->classroom?->id)
            ->where('course_id', $this->course_id ?? $this->course?->id)
            ->where('school_year_id', $this->school_year_id ?? $this->schoolYear?->id)
            ->first();
        $note = $fiche ? (is_string($fiche->note) ? json_decode($fiche->note, true) : $fiche->note) : null;
        $noteKeys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
        if (! is_array($note) || empty($note)) {
            $note = [];
            foreach ($noteKeys as $key) {
                $note[$key] = 0.0;
            }
        } else {
            foreach ($noteKeys as $key) {
                if (! array_key_exists($key, $note)) {
                    $note[$key] = 0.0;
                } elseif ($note[$key] === null) {
                    $note[$key] = 0.0;
                }
            }
            $note = array_intersect_key(array_merge(array_flip($noteKeys), $note), array_flip($noteKeys));
        }

        // Calcul du pourcentage en fonction du maxima de chaque période/examen
        $ficheCourse = $fiche ? $fiche->course : null;
        $maxima = [];
        if ($ficheCourse) {
            $maxima = [
                'P1' => $ficheCourse->max_period_1 ?? 0,
                'P2' => $ficheCourse->max_period_2 ?? 0,
                'P3' => $ficheCourse->max_period_3 ?? 0,
                'P4' => $ficheCourse->max_period_4 ?? 0,
                'E1' => $ficheCourse->max_exam_1 ?? 0,
                'E2' => $ficheCourse->max_exam_2 ?? 0,
            ];
        } else {
            $maxima = array_fill_keys($noteKeys, 0);
        }
        $somme = 0.0;
        $somme_maxima = 0.0;
        foreach ($noteKeys as $key) {
            $somme += (float) $note[$key];
            $somme_maxima += (float) $maxima[$key];
        }
        $pourcentage = $somme_maxima > 0 ? round(($somme / $somme_maxima) * 100, 2) : 0.0;
        $moyenne_note = count($noteKeys) > 0 ? round($somme / count($noteKeys), 2) : 0.0;

        // Affichage du champ repechage uniquement si l'élève n'est PAS validé
        $showRepechage = ! $this->is_validated;

        $data = [
            'id' => $this->id,
            'student' => $this->student ? new \App\Http\Resources\Student\StudentResource($this->student) : null,
            'classroom' => $this->classroom,
            'filiaire' => $this->filiaire,
            'academic_level' => $this->academicLevel,
            'cycle' => $this->cycle,
            'school_year' => $this->schoolYear,
            'school' => $this->school,
            'course' => $this->course,
            'is_validated' => $this->is_validated,
            'mention' => $this->conduiteGrade ? $this->conduiteGrade->name ?? null : null,
            'cotations' => $this->cotations,
            'note' => $note,
            'moyenne_note' => $moyenne_note,
                // Ajout des totaux semestriels calculés
                'semestre_1_total' => round((float)$note['P1'] + (float)$note['P2'] + (float)$note['E1'], 2),
                'semestre_2_total' => round((float)$note['P3'] + (float)$note['P4'] + (float)$note['E2'], 2),
            'pourcentage' => $pourcentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        if ($showRepechage) {
            $data['repechage'] = true;
        }

        return $data;
    }
}
