<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class RepechageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Calcul du pourcentage: priorité à la note saisie (student_score)
        $pourcentage = null;
        if ($this->student_score !== null) {
            $pourcentage = (float) $this->student_score;
        } else {
            // Sinon, calcul à partir de la fiche de cotation (comme ailleurs)
            $fiche = \App\Models\FicheCotation::where('student_id', $this->student_id)
                ->where('classroom_id', $this->classroom_id)
                ->where('course_id', $this->course_id)
                ->where('school_year_id', $this->school_year_id)
                ->with('course')
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
            $ficheCourse = $fiche ? $fiche->course : null;
            $maxima = $ficheCourse ? [
                'P1' => $ficheCourse->max_period_1 ?? 0,
                'P2' => $ficheCourse->max_period_2 ?? 0,
                'P3' => $ficheCourse->max_period_3 ?? 0,
                'P4' => $ficheCourse->max_period_4 ?? 0,
                'E1' => $ficheCourse->max_exam_1 ?? 0,
                'E2' => $ficheCourse->max_exam_2 ?? 0,
            ] : array_fill_keys($noteKeys, 0);

            $somme = 0.0;
            $somme_maxima = 0.0;
            foreach ($noteKeys as $key) {
                $somme += (float) $note[$key];
                $somme_maxima += (float) $maxima[$key];
            }
            $pourcentage = $somme_maxima > 0 ? round(($somme / $somme_maxima) * 100, 2) : 0.0;
        }

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->full_name,
            'school_year_id' => $this->school_year_id,
            'school_year_name' => $this->schoolYear?->name,
            'filiaire_id' => $this->filiaire_id,
            'filiaire_name' => $this->filiaire?->name,
            'classroom_id' => $this->classroom_id,
            'classroom_name' => $this->classroom?->name,
            'course_id' => $this->course_id,
            'course_name' => $this->course?->name,
            'full_name' => $this->full_name,
            'score_percent' => $this->score_percent,
            'student_score' => $this->student_score,
            'pourcentage' => $pourcentage, // nouveau
            'is_eliminated' => $this->is_eliminated,
            'cycle_id' => $this->cycle_id,
            'cycle_name' => $this->cycle?->name,
            'school_id' => $this->school_id,
            'school_name' => $this->school?->name,
            'academic_level_id' => $this->academic_level_id,
            'academic_level_name' => $this->academicLevel?->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
