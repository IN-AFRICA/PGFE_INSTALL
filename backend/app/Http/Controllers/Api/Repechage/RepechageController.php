<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Repechage;

use App\Http\Controllers\Controller;
use App\Http\Resources\RepechageResource;
use App\Models\Repechage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RepechageController extends Controller
{
    public function index(Request $request)
    {
        $schoolYearId = $request->get('school_year_id');
        $classroomId = $request->get('classroom_id');
        $courseId = $request->get('course_id');
        $filiaireId = $request->get('filiaire_id');
        $mode = $request->get('mode'); // 'archive' ou null
        $threshold = (float) $request->get('threshold', 50); // seuil en % pour le repêchage

        if ($mode === 'archive') {
            // Filtrage sur la table repechages (historique)
            if (! $schoolYearId || ! $classroomId || ! $courseId) {
                return response()->json([
                    'error' => 'Vous devez obligatoirement choisir une année scolaire, une classe et un cours pour le filtrage.',
                ], 422);
            }
            $query = Repechage::query()
                ->where('school_year_id', $schoolYearId)
                ->where('classroom_id', $classroomId)
                ->where('course_id', $courseId)
                ->whereNotNull('student_id');
            if ($filiaireId) {
                $query->where('filiaire_id', $filiaireId);
            }
            $repechages = $query->get()->map(function ($rep) {
                // Récupérer la fiche de cotation liée (avec le cours pour éviter le lazy loading)
                $fiche = \App\Models\FicheCotation::where('student_id', $rep->student_id)
                    ->where('classroom_id', $rep->classroom_id)
                    ->where('course_id', $rep->course_id)
                    ->where('school_year_id', $rep->school_year_id)
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
                // Calcul du pourcentage
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

                if ($rep->student_score !== null) {
                    $pourcentage = (float) $rep->student_score;
                }

                return [
                    'id' => $rep->id,
                    'student_id' => $rep->student_id,
                    'full_name' => $rep->full_name ?? null,
                    'classroom_id' => $rep->classroom_id,
                    'course_id' => $rep->course_id,
                    'school_year_id' => $rep->school_year_id,
                    'score_percent' => $rep->score_percent ?? null,
                    'student_score' => $rep->student_score ?? null,
                    'is_eliminated' => $rep->is_eliminated,
                    'cycle_id' => $rep->cycle_id,
                    'school_id' => $rep->school_id,
                    'academic_level_id' => $rep->academic_level_id,
                    'note' => $note,
                    'pourcentage' => $pourcentage,
                    'created_at' => $rep->created_at,
                    'updated_at' => $rep->updated_at,
                ];
            })
            // Ne garder que les élèves en-dessous du seuil
            ->filter(function (array $item) use ($threshold) {
                return $item['pourcentage'] < $threshold;
            })
            ->values();

            return response()->json([
                'data' => $repechages,
                'meta' => [
                    'total' => $repechages->count(),
                ],
                'message' => 'Liste des repêchages archivés récupérée avec succès.',
                'success' => true,
            ], Response::HTTP_OK);
        }
        // Initialisation via la table deliberations
        // Année scolaire implicite (active)
        $activeSchoolYear = \App\Models\SchoolYear::active();
        if (! $activeSchoolYear) {
            return response()->json([
                'error' => "Aucune année scolaire active n'a été trouvée.",
            ], 422);
        }
        // Exiger obligatoirement la classe et le cours
        if (! $classroomId || ! $courseId) {
            return response()->json([
                'error' => 'Vous devez obligatoirement choisir une classe et un cours pour obtenir la liste des élèves au repêchage.',
            ], 422);
        }
        $deliberations = \App\Models\Deliberation::where('is_validated', false)
            ->where('school_year_id', $activeSchoolYear->id)
            ->where('classroom_id', $classroomId)
            ->where('course_id', $courseId)
            ->with(['student'])
            ->get();
        $repechages = $deliberations->map(function ($delib) {
            // Récupérer la fiche de cotation liée (avec cours)
            $fiche = \App\Models\FicheCotation::where('student_id', $delib->student_id)
                ->where('classroom_id', $delib->classroom_id)
                ->where('course_id', $delib->course_id)
                ->where('school_year_id', $delib->school_year_id)
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
            // Calcul du pourcentage
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

            return [
                'id' => $delib->id,
                'student_id' => $delib->student_id,
                'full_name' => $delib->student->full_name ?? null,
                'classroom_id' => $delib->classroom_id,
                'course_id' => $delib->course_id,
                'school_year_id' => $delib->school_year_id,
                'score_percent' => $delib->score_percent ?? null,
                'student_score' => null, // pas encore saisi à ce stade
                'note' => $note,
                'pourcentage' => $pourcentage,
                'created_at' => $delib->created_at,
                'updated_at' => $delib->updated_at,
            ];
        })
        // Ne garder que les élèves en-dessous du seuil
        ->filter(function (array $item) use ($threshold) {
            return $item['pourcentage'] < $threshold;
        })
        ->values();

        return response()->json([
            'data' => $repechages,
        ]);

    }

    public function show(Repechage $repechage)
    {
        return new RepechageResource($repechage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'course_id' => 'required|integer',
            'score_percent' => 'nullable|numeric',
            'student_score' => 'nullable|numeric',
            'is_eliminated' => 'nullable|boolean',
            'full_name' => 'nullable|string',
        ]);

        $deliberation = \App\Models\Deliberation::where('student_id', $data['student_id'])
            ->where('classroom_id', $data['classroom_id'])
            ->where('course_id', $data['course_id'])
            ->where('is_validated', true)
            ->first();
        if (! $deliberation) {
            return response()->json([
                'error' => "L'élève n'est pas au repêchage pour cette classe et ce cours.",
            ], 422);
        }

        // Récupérer l'inscription active de l'élève
        $registration = \App\Models\Registration::where('student_id', $data['student_id'])
            ->where('registration_status', 'active')
            ->first();
        if ($registration) {
            $data['filiaire_id'] = $registration->filiaire_id;
            $data['academic_level_id'] = $registration->academic_level_id;
            $data['cycle_id'] = $registration->cycle_id;
            $data['school_id'] = $registration->school_id;
            $data['school_year_id'] = $registration->school_year_id;
        }

        $item = Repechage::create($data);

        return (new RepechageResource($item))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, Repechage $repechage)
    {
        $data = $request->validate([
            'score_percent' => 'nullable|numeric',
            'student_score' => 'nullable|numeric',
            'is_eliminated' => 'nullable|boolean',
        ]);
        $repechage->update($data);

        return new RepechageResource($repechage);
    }

    public function destroy(Repechage $repechage)
    {
        $repechage->delete();

        return response()->json(['message' => 'Supprimé avec succès.']);
    }

    public function export(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $query = \App\Models\Repechage::with(['student', 'classroom', 'filiaire', 'cycle', 'academicLevel']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $repechages = $query->get();
        $export = new \App\Exports\RepechagesExport($repechages);
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'repechage.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $query = \App\Models\Repechage::with(['student', 'classroom', 'filiaire', 'cycle', 'academicLevel']);
        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }
        $repechages = $query->get();

        $html = view('exports.repechages', [
            'repechages' => $repechages
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'repechage_' . now()->format('Ymd_His') . '.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
