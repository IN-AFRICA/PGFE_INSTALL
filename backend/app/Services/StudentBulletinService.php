<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;

final class StudentBulletinService
{
    /**
     * Charge l'élève et toutes les données nécessaires au bulletin.
     * On déduit filière et année depuis l'inscription si elles ne sont pas fournies.
     */
    public function loadStudent(int $studentId, ?int $schoolYearId = null): Student
    {
        // Charger l'élève et son inscription + méta
        $student = Student::query()
            ->with([
                'school',
                'registration.classroom',
                'registration.filiaire',
                'registration.academicLevel',
                'registration.cycle',
                'registration.schoolYear',
                'registration.type',
                'registration.personal',
                'registration.personal.user',
            ])
            ->findOrFail($studentId);

        // Déduction
        $filiereId = (int) ($student->registration?->filiaire_id ?? 0);
        $effectiveSchoolYearId = $schoolYearId ?: (int) ($student->registration?->school_year_id ?? 0);

        // Charger les relations filtrées
        $student->load([
            'notes' => function ($q) use ($effectiveSchoolYearId) {
                $q->with('course');
                if ($effectiveSchoolYearId) {
                    $q->where('school_year_id', $effectiveSchoolYearId);
                }
            },
            'conduiteGrades' => function ($q) use ($filiereId, $effectiveSchoolYearId) {
                $q->with(['conduiteSemester1.semester', 'conduiteSemester2.semester']);
                if ($filiereId) {
                    $q->where('filiere_id', $filiereId);
                }
                if ($effectiveSchoolYearId) {
                    $q->where('school_year_id', $effectiveSchoolYearId);
                }
            },
            'deliberations' => function ($q) use ($filiereId, $effectiveSchoolYearId) {
                if ($filiereId) {
                    $q->where('filiaire_id', $filiereId);
                }
                if ($effectiveSchoolYearId) {
                    $q->where('school_year_id', $effectiveSchoolYearId);
                }
            },
            'repechages' => function ($q) use ($filiereId, $effectiveSchoolYearId) {
                $q->with(['classroom', 'course', 'filiaire', 'cycle', 'academicLevel', 'schoolYear']);
                if ($filiereId) {
                    $q->where('filiaire_id', $filiereId);
                }
                if ($effectiveSchoolYearId) {
                    $q->where('school_year_id', $effectiveSchoolYearId);
                }
            },
        ]);

        // Raccourcis pour la vue Blade
        if ($student->relationLoaded('registration')) {
            $student->setRelation('classroom', $student->registration?->classroom);
            $student->setRelation('filiere', $student->registration?->filiaire);
        }

        // S'assurer que tous les cours de la classe sont présents dans le bulletin
        $classroomId = (int) ($student->registration?->classroom_id ?? 0);
        if ($classroomId) {
            $allClassroomCourses = \App\Models\Course::where('classroom_id', $classroomId)->get();
            $existingNotes = $student->notes ?? collect();
            $mergedNotes = collect();

            foreach ($allClassroomCourses as $course) {
                $note = $existingNotes->firstWhere('course_id', $course->id);
                if (!$note) {
                    $note = new \App\Models\FicheCotation([
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'school_year_id' => $effectiveSchoolYearId,
                        'note' => json_encode(['P1'=>0,'P2'=>0,'P3'=>0,'P4'=>0,'E1'=>0,'E2'=>0]),
                        'classroom_id' => $classroomId,
                    ]);
                    $note->setRelation('course', $course);
                }
                $mergedNotes->push($note);
            }
            $student->setRelation('notes', $mergedNotes);
        }

        // Calculs supplémentaires pour le bulletin: effectif de la classe, rang et pourcentage global
        $classroomId = (int) ($student->registration?->classroom_id ?? 0);
        if ($classroomId && $effectiveSchoolYearId) {
            // 1) Effectif de la classe pour l'année scolaire
            $classSize = \App\Models\Registration::query()
                ->where('classroom_id', $classroomId)
                ->where('school_year_id', $effectiveSchoolYearId)
                ->count();

            // 2) Pourcentage global de l'élève
            [$overallPercent, $overallOn20] = $this->computeOverallFromNotes($student);

            // 2bis) Totaux globaux par période (P1..P4) et examen (E1..E2)
            $periodExamTotals = $this->computeTotalsByKeyFromNotes($student);

            // 3) Classement: calcul du pourcentage global de tous les élèves de la classe
            $allNotes = \App\Models\FicheCotation::query()
                ->with('course')
                ->where('classroom_id', $classroomId)
                ->where('school_year_id', $effectiveSchoolYearId)
                ->get()
                ->groupBy('student_id');

            // Tableau associatif student_id => pourcentage global
            $percentByStudent = [];
            // Totaux par période / examen pour chaque élève de la classe
            $keys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
            $perKeyTotalsByStudent = [];

            foreach ($allNotes as $sid => $notes) {
                [$p, $_on20] = $this->computeOverallFromRawNotes($notes);
                $sid = (int) $sid;
                $percentByStudent[$sid] = (float) $p;

                // Totaux par période / examen pour ce camarade
                $obtained = array_fill_keys($keys, 0.0);
                $maxima = array_fill_keys($keys, 0.0);

                foreach ($notes as $n) {
                    $noteRaw = is_string($n->note) ? json_decode($n->note, true) : $n->note;
                    if (! is_array($noteRaw)) {
                        $noteRaw = [];
                    }

                    $course = $n->course;
                    $courseMax = $course ? [
                        'P1' => (float) ($course->max_period_1 ?? 0),
                        'P2' => (float) ($course->max_period_2 ?? 0),
                        'P3' => (float) ($course->max_period_3 ?? 0),
                        'P4' => (float) ($course->max_period_4 ?? 0),
                        'E1' => (float) ($course->max_exam_1 ?? 0),
                        'E2' => (float) ($course->max_exam_2 ?? 0),
                    ] : array_fill_keys($keys, 0.0);

                    foreach ($keys as $k) {
                        $obtained[$k] += (float) ($noteRaw[$k] ?? 0.0);
                        $maxima[$k] += (float) ($courseMax[$k] ?? 0.0);
                    }
                }

                $perKeyTotalsByStudent[$sid] = [
                    'obtained' => $obtained,
                    'maxima' => $maxima,
                ];
            }

            // S'assurer que l'élève courant est toujours présent dans le classement
            // même si aucune FicheCotation n'est trouvée pour lui.
            $studentId = (int) $student->id;
            $percentByStudent[$studentId] = (float) $overallPercent;

            // S'assurer également que les totaux par période / examen existent pour lui
            if (! isset($perKeyTotalsByStudent[$studentId])
                && is_array($periodExamTotals)
                && isset($periodExamTotals['obtained'], $periodExamTotals['maxima'])
                && is_array($periodExamTotals['obtained'])
                && is_array($periodExamTotals['maxima'])) {
                $perKeyTotalsByStudent[$studentId] = [
                    'obtained' => $periodExamTotals['obtained'],
                    'maxima' => $periodExamTotals['maxima'],
                ];
            }

            // Construire la liste triée des étudiants par pourcentage décroissant
            $ranking = [];
            foreach ($percentByStudent as $sid => $p) {
                $ranking[] = [
                    'student_id' => (int) $sid,
                    'percent' => (float) $p,
                ];
            }

            usort($ranking, function ($a, $b) {
                return $b['percent'] <=> $a['percent'];
            });

            $rank = null;
            foreach ($ranking as $idx => $row) {
                if ((int) $row['student_id'] === (int) $studentId) {
                    $rank = $idx + 1; // position 1-based
                    break;
                }
            }

            // Classement par période / examen pour l'élève courant
            $periodExamRanks = [];
            // Classement par semestre (S1 = P1+P2+E1, S2 = P3+P4+E2) pour l'élève courant
            $semesterRanks = [];
            // Totaux par semestre pour l'élève courant
            $semesterTotalsCurrent = [];

            if (! empty($perKeyTotalsByStudent)) {
                // 3a) Rangs par période / examen
                foreach ($keys as $k) {
                    $rankingKey = [];
                    foreach ($perKeyTotalsByStudent as $sid => $totals) {
                        $obt = (float) ($totals['obtained'][$k] ?? 0.0);
                        $max = (float) ($totals['maxima'][$k] ?? 0.0);
                        $percent = $max > 0.0 ? round(($obt / $max) * 100, 2) : 0.0;

                        $rankingKey[] = [
                            'student_id' => (int) $sid,
                            'percent' => $percent,
                        ];
                    }

                    usort($rankingKey, function ($a, $b) {
                        return $b['percent'] <=> $a['percent'];
                    });

                    $keyRank = null;
                    foreach ($rankingKey as $idx => $row) {
                        if ((int) $row['student_id'] === $studentId) {
                            $keyRank = $idx + 1; // position 1-based
                            break;
                        }
                    }

                    $periodExamRanks[$k] = $keyRank;
                }

                // 3b) Rangs par semestre (S1 = P1+P2+E1, S2 = P3+P4+E2)
                $semesters = [
                    'S1' => ['P1', 'P2', 'E1'],
                    'S2' => ['P3', 'P4', 'E2'],
                ];

                foreach ($semesters as $semKey => $semKeys) {
                    $rankingSem = [];

                    foreach ($perKeyTotalsByStudent as $sid => $totals) {
                        $obt = 0.0;
                        $max = 0.0;
                        foreach ($semKeys as $k) {
                            $obt += (float) ($totals['obtained'][$k] ?? 0.0);
                            $max += (float) ($totals['maxima'][$k] ?? 0.0);
                        }

                        $percent = $max > 0.0 ? round(($obt / $max) * 100, 2) : 0.0;

                        $rankingSem[] = [
                            'student_id' => (int) $sid,
                            'percent' => $percent,
                        ];

                        // Mémoriser les totaux pour l'élève courant
                        if ((int) $sid === $studentId) {
                            $semesterTotalsCurrent[$semKey] = [
                                'obtained' => $obt,
                                'maxima' => $max,
                            ];
                        }
                    }

                    usort($rankingSem, function ($a, $b) {
                        return $b['percent'] <=> $a['percent'];
                    });

                    $semRank = null;
                    foreach ($rankingSem as $idx => $row) {
                        if ((int) $row['student_id'] === $studentId) {
                            $semRank = $idx + 1; // position 1-based
                            break;
                        }
                    }

                    $semesterRanks[$semKey] = $semRank;
                }
            }

            // 4) Statuts de délibération et repêchage
            $hasRepechage = $student->relationLoaded('repechages') ? $student->repechages->isNotEmpty() : false;
            $delibStatus = null;
            if ($student->relationLoaded('deliberations') && $student->deliberations->isNotEmpty()) {
                // Si au moins une délibération validée, considéré "passe"
                $anyValidated = (bool) $student->deliberations->firstWhere('is_validated', true);
                $delibStatus = $anyValidated ? 'passe' : 'double';
            }

            // Injecter les méta calculées sur le modèle pour usage dans la Resource
            $student->setAttribute('bulletin_meta', [
                'class_size' => $classSize,
                'rank' => $rank,
                'overall_percent' => $overallPercent,
                'overall_on_20' => $overallOn20,
                'period_exam_totals' => $periodExamTotals,
                'period_exam_ranks' => $periodExamRanks,
                'semester_totals' => $semesterTotalsCurrent,
                'semester_ranks' => $semesterRanks,
                'deliberation' => $delibStatus,
                'has_repechage' => $hasRepechage,
            ]);
        }

        return $student;
    }

    /**
     * Calcule le pourcentage global et la moyenne /20 à partir des notes déjà chargées sur l'élève.
     */
    private function computeOverallFromNotes(Student $student): array
    {
        if (! $student->relationLoaded('notes')) {
            return [0.0, 0.0];
        }

        $keys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
        $sumAll = 0.0;
        $sumMaxAll = 0.0;

        foreach ($student->notes as $n) {
            $noteRaw = is_string($n->note) ? json_decode($n->note, true) : $n->note;
            if (! is_array($noteRaw)) {
                $noteRaw = [];
            }
            $course = $n->course;
            $maxima = $course ? [
                'P1' => (float) ($course->max_period_1 ?? 0),
                'P2' => (float) ($course->max_period_2 ?? 0),
                'P3' => (float) ($course->max_period_3 ?? 0),
                'P4' => (float) ($course->max_period_4 ?? 0),
                'E1' => (float) ($course->max_exam_1 ?? 0),
                'E2' => (float) ($course->max_exam_2 ?? 0),
            ] : array_fill_keys($keys, 0.0);

            foreach ($keys as $k) {
                $v = (float) ($noteRaw[$k] ?? 0.0);
                $sumAll += $v;
                $sumMaxAll += (float) ($maxima[$k] ?? 0.0);
            }
        }

        $percent = $sumMaxAll > 0 ? round(($sumAll / $sumMaxAll) * 100, 2) : 0.0;
        $on20 = $sumMaxAll > 0 ? round(($sumAll / $sumMaxAll) * 20, 2) : 0.0;

        return [$percent, $on20];
    }

    /**
     * Calcule les totaux globaux obtenus et maxima pour chaque période (P1..P4)
     * et chaque examen (E1, E2) à partir des notes chargées sur l'élève.
     *
     * @return array{
     *     obtained: array<string, float>,
     *     maxima: array<string, float>
     * }
     */
    private function computeTotalsByKeyFromNotes(Student $student): array
    {
        if (! $student->relationLoaded('notes')) {
            return [
                'obtained' => [],
                'maxima' => [],
            ];
        }

        $keys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
        $obtainedTotals = array_fill_keys($keys, 0.0);
        $maximaTotals = array_fill_keys($keys, 0.0);

        foreach ($student->notes as $n) {
            $noteRaw = is_string($n->note) ? json_decode($n->note, true) : $n->note;
            if (! is_array($noteRaw)) {
                $noteRaw = [];
            }

            $course = $n->course;
            $maxima = $course ? [
                'P1' => (float) ($course->max_period_1 ?? 0.0),
                'P2' => (float) ($course->max_period_2 ?? 0.0),
                'P3' => (float) ($course->max_period_3 ?? 0.0),
                'P4' => (float) ($course->max_period_4 ?? 0.0),
                'E1' => (float) ($course->max_exam_1 ?? 0.0),
                'E2' => (float) ($course->max_exam_2 ?? 0.0),
            ] : array_fill_keys($keys, 0.0);

            foreach ($keys as $k) {
                $obtainedTotals[$k] += (float) ($noteRaw[$k] ?? 0.0);
                $maximaTotals[$k] += (float) ($maxima[$k] ?? 0.0);
            }
        }

        // Calcul des totaux par semestre et totaux généraux
        $obtainedTotals['TOT1'] = $obtainedTotals['P1'] + $obtainedTotals['P2'] + $obtainedTotals['E1'];
        $maximaTotals['TOT1'] = $maximaTotals['P1'] + $maximaTotals['P2'] + $maximaTotals['E1'];
        $obtainedTotals['S1'] = $obtainedTotals['TOT1'];
        $maximaTotals['S1'] = $maximaTotals['TOT1'];

        $obtainedTotals['TOT2'] = $obtainedTotals['P3'] + $obtainedTotals['P4'] + $obtainedTotals['E2'];
        $maximaTotals['TOT2'] = $maximaTotals['P3'] + $maximaTotals['P4'] + $maximaTotals['E2'];
        $obtainedTotals['S2'] = $obtainedTotals['TOT2'];
        $maximaTotals['S2'] = $maximaTotals['TOT2'];

        $obtainedTotals['TG'] = $obtainedTotals['TOT1'] + $obtainedTotals['TOT2'];
        $maximaTotals['TG'] = $maximaTotals['TOT1'] + $maximaTotals['TOT2'];

        return [
            'obtained' => $obtainedTotals,
            'maxima' => $maximaTotals,
        ];
    }

    /**
     * Même calcul que ci-dessus mais à partir d'une collection de FicheCotation groupée par élève.
     * @param \Illuminate\Support\Collection<int, \App\Models\FicheCotation> $notes
     */
    private function computeOverallFromRawNotes($notes): array
    {
        $keys = ['P1', 'P2', 'P3', 'P4', 'E1', 'E2'];
        $sumAll = 0.0;
        $sumMaxAll = 0.0;

        foreach ($notes as $n) {
            $noteRaw = is_string($n->note) ? json_decode($n->note, true) : $n->note;
            if (! is_array($noteRaw)) {
                $noteRaw = [];
            }
            $course = $n->course;
            $maxima = $course ? [
                'P1' => (float) ($course->max_period_1 ?? 0),
                'P2' => (float) ($course->max_period_2 ?? 0),
                'P3' => (float) ($course->max_period_3 ?? 0),
                'P4' => (float) ($course->max_period_4 ?? 0),
                'E1' => (float) ($course->max_exam_1 ?? 0),
                'E2' => (float) ($course->max_exam_2 ?? 0),
            ] : array_fill_keys($keys, 0.0);

            foreach ($keys as $k) {
                $v = (float) ($noteRaw[$k] ?? 0.0);
                $sumAll += $v;
                $sumMaxAll += (float) ($maxima[$k] ?? 0.0);
            }
        }

        $percent = $sumMaxAll > 0 ? round(($sumAll / $sumMaxAll) * 100, 2) : 0.0;
        $on20 = $sumMaxAll > 0 ? round(($sumAll / $sumMaxAll) * 20, 2) : 0.0;

        return [$percent, $on20];
    }
}
