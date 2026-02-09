<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\DisciplinaryActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConduiteGradeRequest;
use App\Http\Resources\ConduitesGradesResource;
use App\Models\ConduiteGrade;
use Exception;
use Illuminate\Http\Request;

final class ConduiteGradeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $grades = ConduiteGrade::query()
                // Eager-load only real relations
                ->with([
                    'schoolYear',
                    'filiere',
                    'classroom',
                    'student',
                    'conduiteSemester1',
                    'conduiteSemester2',
                ])
                // Classroom filter supports classroom_id or legacy idClasse
                ->when(true, function ($query) use ($request) {
                    $classroomId = $request->input('classroom_id', $request->input('idClasse'));
                    if (is_numeric($classroomId) && (int) $classroomId > 0) {
                        $query->where('classroom_id', (int) $classroomId);
                    }

                    return $query;
                })
                // School year filter
                ->when(true, function ($query) use ($request) {
                    $val = $request->input('school_year_id');
                    if (is_numeric($val) && (int) $val > 0) {
                        $query->where('school_year_id', (int) $val);
                    }

                    return $query;
                })
                // Filiere filter
                ->when(true, function ($query) use ($request) {
                    $val = $request->input('filiere_id');
                    if (is_numeric($val) && (int) $val > 0) {
                        $query->where('filiere_id', (int) $val);
                    }

                    return $query;
                })
                // Student filter
                ->when(true, function ($query) use ($request) {
                    $val = $request->input('student_id');
                    if (is_numeric($val) && (int) $val > 0) {
                        $query->where('student_id', (int) $val);
                    }

                    return $query;
                })
                // Conduite semester filters
                ->when(true, function ($query) use ($request) {
                    $val1 = $request->input('conduite_semester_1_id');
                    if (is_numeric($val1) && (int) $val1 > 0) {
                        $query->where('conduite_semester_1_id', (int) $val1);
                    }
                    $val2 = $request->input('conduite_semester_2_id');
                    if (is_numeric($val2) && (int) $val2 > 0) {
                        $query->where('conduite_semester_2_id', (int) $val2);
                    }

                    return $query;
                })
                // Single date or date range on created_at
                ->when(true, function ($query) use ($request) {
                    $date = $request->input('date');
                    if ($date && strtotime($date) !== false) {
                        $query->whereDate('created_at', $date);
                    }
                    $from = $request->input('date_from');
                    $to = $request->input('date_to');
                    $fromValid = $from && strtotime($from) !== false;
                    $toValid = $to && strtotime($to) !== false;
                    if ($fromValid && $toValid) {
                        $query->whereBetween('created_at', [$from, $to]);
                    } elseif ($fromValid) {
                        $query->whereDate('created_at', '>=', $from);
                    } elseif ($toValid) {
                        $query->whereDate('created_at', '<=', $to);
                    }

                    return $query;
                })
                // Search by student identity, classroom or filiere
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = strtolower(trim((string) $request->input('search')));

                    if ($search === '') {
                        return $query;
                    }

                    return $query->where(function ($q) use ($search) {
                        $q->whereHas('student', function ($qs) use ($search) {
                            $qs->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                        })
                        ->orWhereHas('classroom', function ($qc) use ($search) {
                            $qc->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        })
                        ->orWhereHas('filiere', function ($qf) use ($search) {
                            $qf->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                        });
                    });
                })
                ->orderByDesc('id')
                ->get();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des grades de conduite: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => ConduitesGradesResource::collection($grades),
            'success' => true,
            'message' => 'Liste des grades de conduite',
        ], 200);
    }

    public function store(ConduiteGradeRequest $request)
    {
        try {
            $grade = ConduiteGrade::create($request->validated());

            return response()->json([
                'data' => $grade,
                'success' => true,
                'message' => 'Grade de conduite créé avec succès.',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du grade de conduite: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(ConduiteGrade $conduiteGrade)
    {
        return response()->json([
            'data' => $conduiteGrade,
            'success' => true,
            'message' => 'Grade de conduite récupéré avec succès.',
        ], 200);
    }

    public function update(ConduiteGradeRequest $request, ConduiteGrade $conduiteGrade)
    {
        try {
            $conduiteGrade->update($request->validated());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du grade de conduite: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $conduiteGrade,
            'success' => true,
            'message' => 'Grade de conduite mis à jour avec succès.',
        ], 200);
    }

    public function destroy(ConduiteGrade $conduiteGrade)
    {
        try {
            $conduiteGrade->delete();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du grade de conduite: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grade de conduite supprimé avec succès.',
        ], 204);
    }
}
