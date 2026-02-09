<?php

declare(strict_types=1);

namespace App\Http\Resources\BulletinRessource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StudentBulletinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registration = $this->whenLoaded('registration', fn () => [
            'school' => [
                'id' => $this->school?->id,
                'name' => $this->school?->name,
                'code' => $this->school?->code ?? null,
            ],
            'classroom' => [
                'id' => $this->registration?->classroom?->id,
                'name' => $this->registration?->classroom?->name,
            ],
            'filiere' => [
                'id' => $this->registration?->filiaire?->id,
                'name' => $this->registration?->filiaire?->name,
            ],
            'academic_level' => [
                'id' => $this->registration?->academicLevel?->id,
                'name' => $this->registration?->academicLevel?->name,
            ],
            'cycle' => [
                'id' => $this->registration?->cycle?->id,
                'name' => $this->registration?->cycle?->name,
            ],
            'school_year' => [
                'id' => $this->registration?->schoolYear?->id ?? $request->input('school_year_id'),
                'name' => $this->registration?->schoolYear?->name,
            ],
            'type' => [
                'id' => $this->registration?->type?->id,
                'name' => $this->registration?->type?->title,
            ],
            'personal' => [
                'id' => $this->registration?->personal?->id,
                'user' => [
                    'id' => $this->registration?->personal?->user?->id,
                    'name' => $this->registration?->personal?->user?->name,
                    'email' => $this->registration?->personal?->user?->email,
                ],
            ],
        ]);

        $grades = $this->whenLoaded('notes', function () {
            return $this->notes->map(function ($n) {
                $noteRaw = is_string($n->note) ? json_decode($n->note, true) : $n->note;
                $course = $n->course;
                $noteKeys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];

                if (! is_array($noteRaw) || empty($noteRaw)) {
                    $note = [];
                    foreach ($noteKeys as $key) {
                        $note[$key] = 0.0;
                    }
                } else {
                    $note = $noteRaw;
                    foreach ($noteKeys as $key) {
                        if (! array_key_exists($key, $note) || $note[$key] === null) {
                            $note[$key] = 0.0;
                        }
                    }
                    $note = array_intersect_key(array_merge(array_flip($noteKeys), $note), array_flip($noteKeys));
                }

                $maxima = null;
                if ($course) {
                    $maxima = [
                        'P1' => $course->max_period_1 ?? 0.0,
                        'P2' => $course->max_period_2 ?? 0.0,
                        'P3' => $course->max_period_3 ?? 0.0,
                        'P4' => $course->max_period_4 ?? 0.0,
                        'E1' => $course->max_exam_1 ?? 0.0,
                        'E2' => $course->max_exam_2 ?? 0.0,
                    ];
                }

                $sum = 0.0;
                $sumMax = 0.0;
                foreach ($noteKeys as $key) {
                    $sum += (float) $note[$key];
                    $sumMax += (float) ($maxima[$key] ?? 0.0);
                }

                // Totaux par semestre : (P1+P2+E1) et (P3+P4+E2)
                $sem1Keys = ['P1', 'P2', 'E1'];
                $sem2Keys = ['P3', 'P4', 'E2'];

                $sem1Total = 0.0;
                $sem1Max = 0.0;
                foreach ($sem1Keys as $k) {
                    $sem1Total += (float) ($note[$k] ?? 0.0);
                    $sem1Max += (float) ($maxima[$k] ?? 0.0);
                }

                $sem2Total = 0.0;
                $sem2Max = 0.0;
                foreach ($sem2Keys as $k) {
                    $sem2Total += (float) ($note[$k] ?? 0.0);
                    $sem2Max += (float) ($maxima[$k] ?? 0.0);
                }

                $averagePercent = $sumMax > 0 ? round(($sum / $sumMax) * 100, 2) : 0.0;
                $averageOn20 = $sumMax > 0 ? round(($sum / $sumMax) * 20, 2) : 0.0;

                return [
                    'id' => (int) $n->id,
                    'course_id' => $n->course_id,
                    'course' => $course ? ($course->name ?? $course->label ?? null) : null,
                    'note' => $note,
                    'maxima' => $maxima,
                    'total_obtained' => $sum,
                    'total_maxima' => $sumMax,
                    'sem1_total' => $sem1Total,
                    'sem1_maxima' => $sem1Max,
                    'sem2_total' => $sem2Total,
                    'sem2_maxima' => $sem2Max,
                    'average_percent' => $averagePercent,
                    'average_on_20' => $averageOn20,
                ];
            });
        });

        $repechages = $this->whenLoaded('repechages', function () {
            return $this->repechages->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'course' => [
                        'id' => $r->course?->id,
                        'name' => $r->course?->name,
                    ],
                    'classroom' => [
                        'id' => $r->classroom?->id,
                        'name' => $r->classroom?->name,
                    ],
                    'filiaire' => [
                        'id' => $r->filiaire?->id,
                        'name' => $r->filiaire?->name,
                    ],
                    'cycle' => [
                        'id' => $r->cycle?->id,
                        'name' => $r->cycle?->name,
                    ],
                    'academic_level' => [
                        'id' => $r->academicLevel?->id,
                        'name' => $r->academicLevel?->name,
                    ],
                    'school_year' => [
                        'id' => $r->schoolYear?->id,
                        'name' => $r->schoolYear?->name,
                    ],
                    'score_percent' => $r->score_percent,
                    'student_score' => $r->student_score,
                    'is_eliminated' => (bool) $r->is_eliminated,
                ];
            });
        });

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'student' => [
                'id' => $this->id,
                'full_name' => mb_trim(($this->firstname ?? '').' '.($this->name ?? '')),
                'lastname' => $this->lastname,
                'firstname' => $this->firstname,
                'gender' => $this->gender?->value ?? $this->gender ?? null,
                'birth_date' => optional($this->birth_date)->format('Y-m-d'),
                'birth_place' => $this->birth_place,
                'matricule' => $this->matricule,
                'permanent_number' => $this->matricule, // alias demandé
                'address' => $this->address,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'image_url' => $this->image_url ?? null,
            ],
            'registration' => $registration,

            'grades' => $grades,
            'repechages' => $repechages,

            'conduite' => $this->whenLoaded('conduiteGrades', fn () => $this->conduiteGrades->map(fn ($c) => [
                'id' => $c->id,
                'fault_count' => isset($c->fault_count) ? (int) $c->fault_count : null,
                'conduite_semester_1' => optional($c->conduiteSemester1->semester ?? null)->name,
                'conduite_semester_2' => optional($c->conduiteSemester2->semester ?? null)->name,
            ])),

            'deliberations' => $this->whenLoaded('deliberations', fn () => $this->deliberations->map(fn ($d) => [
                'id' => $d->id,
                'average' => $d->average ?? null,
                'decision' => $d->decision ?? null,
                'is_validated' => $d->is_validated ?? null,
                'school_year_id' => $d->school_year_id ?? null,
            ])),

            // Récapitulatif demandé (nombre d'élèves, place, pourcentage, etc.)
            'summary' => (function () {
                $meta = (array) ($this->bulletin_meta ?? []);

                $classSize = $meta['class_size'] ?? null;
                $rank = $meta['rank'] ?? null;
                $periodExamRanks = $meta['period_exam_ranks'] ?? null;
                $semesterTotals = $meta['semester_totals'] ?? null;
                $semesterRanks = $meta['semester_ranks'] ?? null;

                // Chaîne de place lisible, ex: "3 / 40"
                $placeLabel = null;
                if ($classSize && $rank) {
                    $placeLabel = $rank.' / '.$classSize;
                }

                // Calcul des pourcentages par période / examen à partir des totaux globaux
                $periodExamTotals = $meta['period_exam_totals'] ?? null;
                $periodExamPercent = null;
                $periodExamOn20 = null;

                if (is_array($periodExamTotals)
                    && isset($periodExamTotals['obtained'], $periodExamTotals['maxima'])
                    && is_array($periodExamTotals['obtained'])
                    && is_array($periodExamTotals['maxima'])) {
                    $keys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
                    $periodExamPercent = [];
                    $periodExamOn20 = [];

                    foreach ($keys as $k) {
                        $obt = (float) ($periodExamTotals['obtained'][$k] ?? 0.0);
                        $max = (float) ($periodExamTotals['maxima'][$k] ?? 0.0);

                        if ($max > 0.0) {
                            $percent = round(($obt / $max) * 100, 2);
                            $on20 = round(($obt / $max) * 20, 2);
                        } else {
                            $percent = 0.0;
                            $on20 = 0.0;
                        }

                        $periodExamPercent[$k] = $percent;
                        $periodExamOn20[$k] = $on20;
                    }
                }

                // Construction des places par période / examen (rang / effectif + pourcentage)
                $periodExamPlace = null;
                if (is_array($periodExamRanks) && $classSize) {
                    $periodExamPlace = [];
                    foreach ($periodExamRanks as $k => $r) {
                        if ($r === null) {
                            $periodExamPlace[$k] = null;
                            continue;
                        }

                        $percent = is_array($periodExamPercent) ? ($periodExamPercent[$k] ?? null) : null;
                        $on20 = is_array($periodExamOn20) ? ($periodExamOn20[$k] ?? null) : null;

                        $periodExamPlace[$k] = [
                            'rank' => $r,
                            'out_of' => $classSize,
                            'label' => $r.' / '.$classSize,
                            'percent' => $percent,
                            'on_20' => $on20,
                        ];
                    }
                }

                // Construction des infos par semestre (S1, S2) : place + pourcentage
                $semesterPlace = null;
                if (is_array($semesterRanks) && is_array($semesterTotals) && $classSize) {
                    $semesterPlace = [];
                    foreach ($semesterRanks as $semKey => $semRank) {
                        if ($semRank === null) {
                            $semesterPlace[$semKey] = null;
                            continue;
                        }

                        $obt = (float) ($semesterTotals[$semKey]['obtained'] ?? 0.0);
                        $max = (float) ($semesterTotals[$semKey]['maxima'] ?? 0.0);

                        if ($max > 0.0) {
                            $percent = round(($obt / $max) * 100, 2);
                            $on20 = round(($obt / $max) * 20, 2);
                        } else {
                            $percent = 0.0;
                            $on20 = 0.0;
                        }

                        $semesterPlace[$semKey] = [
                            'rank' => $semRank,
                            'out_of' => $classSize,
                            'label' => $semRank.' / '.$classSize,
                            'percent' => $percent,
                            'on_20' => $on20,
                        ];
                    }
                }

                return [
                    'class_size' => $classSize,
                    'rank' => $rank,
                    // Position de l'élève dans la classe (rang / effectif)
                    'position' => [
                        'rank' => $rank,
                        'out_of' => $classSize,
                    ],
                    // Label direct de place
                    'place' => $placeLabel,
                    'overall_percent' => $meta['overall_percent'] ?? null,
                    'overall_on_20' => $meta['overall_on_20'] ?? null,
                    // Totaux globaux par période (P1..P4) et examen (E1, E2)
                    'period_exam_totals' => $periodExamTotals,
                    // Classement (place) par période / examen, incluant aussi pourcentage et note /20
                    'period_exam_ranks' => $periodExamRanks,
                    'period_exam_place' => $periodExamPlace,
                    // Infos par semestre (S1, S2) : rang, effectif, pourcentage et note /20
                    'semester_totals' => $semesterTotals,
                    'semester_ranks' => $semesterRanks,
                    'semester_place' => $semesterPlace,
                    'application' => null, // en attente de source métier
                    'conduite' => $this->whenLoaded('conduiteGrades', fn () => $this->conduiteGrades->map(fn ($c) => [
                        'fault_count' => isset($c->fault_count) ? (int) $c->fault_count : null,
                        'sem1' => optional($c->conduiteSemester1->semester ?? null)->name,
                        'sem2' => optional($c->conduiteSemester2->semester ?? null)->name,
                    ])),
                    'deliberation' => $meta['deliberation'] ?? null, // 'passe' | 'double'
                    'repechage' => $meta['has_repechage'] ?? false,
                    'school_code' => $this->school?->code ?? null,
                ];
            })(),
        ];
    }
}
