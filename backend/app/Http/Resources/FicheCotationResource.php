<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FicheCotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Décoder la note (JSON)
        $note = is_string($this->note) ? json_decode($this->note, true) : $this->note;
        // Récupérer le cours
        $course = $this->course ?? $this->whenLoaded('course');
        $maxima = null;
        $noteKeys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
        if ($course) {
            $maxima = [
                'P1' => $course->max_period_1 ?? null,
                'P2' => $course->max_period_2 ?? null,
                'P3' => $course->max_period_3 ?? null,
                'P4' => $course->max_period_4 ?? null,
                'E1' => $course->max_exam_1 ?? null,
                'E2' => $course->max_exam_2 ?? null,
            ];
        }
        // Toujours retourner un tableau pour note
        if (! is_array($note) || empty($note)) {
            // Si aucune note, retourner un tableau avec toutes les clés à null
            $note = [];
            foreach ($noteKeys as $key) {
                $note[$key] = 0.0;
            }
        } else {
            // S'assurer que toutes les clés sont présentes
            foreach ($noteKeys as $key) {
                if (! array_key_exists($key, $note)) {
                    $note[$key] = 0.0;
                } elseif ($note[$key] === null) {
                    $note[$key] = 0.0;
                }
            }
            // Réordonner les clés
            $note = array_intersect_key(array_merge(array_flip($noteKeys), $note), array_flip($noteKeys));
        }

        return [
            'id' => (int) $this->id,
            'school_year_id' => $this->registration?->school_year_id,
            'school_year' => $this->registration?->schoolYear?->name,
            'student_id' => $this->registration?->student_id,
            'student' => $this->registration?->student ? mb_trim(($this->registration->student->firstname ?? '').' '.($this->registration->student->name ?? '')) : ($this->registration?->student?->lastname ?? null),
            'classroom_id' => $this->registration?->classroom_id,
            'classroom' => $this->registration?->classroom?->name,
            'course_id' => $this->course_id !== null ? (int) $this->course_id : null,
            'course' => $course ? ($course->name ?? $course->label ?? null) : null,
            'note' => $note,
            'maxima' => $maxima,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
