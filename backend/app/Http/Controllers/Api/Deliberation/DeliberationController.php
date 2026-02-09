<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Deliberation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Deliberation\DeliberationRequest;
use App\Http\Resources\Deliberation\DeliberationResource;
use App\Models\Deliberation;
use Illuminate\Http\Response;

final class DeliberationController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = $request->user();

        // On récupère l'année scolaire par défaut (celle active ou celle de l'utilisateur)
        $schoolYearId = $request->input('school_year_id')
            ?? ($user->school_year_id ?? \App\Models\SchoolYear::where('is_active', true)->value('id'));

        $query = Deliberation::query();

        // Filtrage par école de l'utilisateur connecté
        if ($user && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        // Filtrage par année scolaire (par défaut celle de l'utilisateur ou active)
        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        // Filtrage par classe
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filtrage par cours
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Nouveau filtrage selon le paramètre 'initialized'
        if ($request->has('initialized')) {
            $initialized = $request->boolean('initialized');
            $query->where('is_validated', $initialized);
        }
        // Sinon, on ne filtre pas sur is_validated, on retourne tout

        // Recherche filtrante
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(matricule) LIKE ?', ["%{$search}%"]);
                })
                ->orWhereHas('course', function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(label) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        $deliberations = $query
            ->with([
                'student',
                'cotations',
                'classroom',
                'filiaire',
                'academicLevel',
                'cycle',
                'schoolYear',
                'school',
                'course',
                'registration',
                'conduiteGrade',
            ])
            ->get();

        return DeliberationResource::collection($deliberations);
    }

    public function store(DeliberationRequest $request)
    {
        $data = $request->validated();

        // Détermination automatique de l'année scolaire si non fournie
        $schoolYearId = $data['school_year_id']
            ?? ($request->user()->school_year_id ?? \App\Models\SchoolYear::where('is_active', true)->value('id'));
        $classroomId = $data['classroom_id'] ?? null;
        $courseId = $data['course_id'] ?? null;

        if (! $classroomId || ! $courseId || ! $schoolYearId) {
            return response()->json([
                'message' => 'classroom_id, course_id et school_year_id sont obligatoires.',
            ], 422);
        }

        // Récupérer tous les élèves inscrits dans cette classe et année
        $registrations = \App\Models\Registration::where('classroom_id', $classroomId)
            ->where('school_year_id', $schoolYearId)
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'message' => 'Aucun élève trouvé pour cette classe et cette année scolaire.',
            ], 404);
        }

        $created = [];
        foreach ($registrations as $r) {
            $exists = Deliberation::where('classroom_id', $classroomId)
                ->where('course_id', $courseId)
                ->where('school_year_id', $schoolYearId)
                ->where('student_id', $r->student_id)
                ->exists();
            if (! $exists) {
                $deliberation = Deliberation::create([
                    'student_id' => $r->student_id,
                    'classroom_id' => $classroomId,
                    'course_id' => $courseId,
                    'school_year_id' => $schoolYearId,
                    'filiaire_id' => $r->filiaire_id ?? null,
                    'academic_level_id' => $r->academic_level_id ?? null,
                    'cycle_id' => $r->cycle_id ?? null,
                    'school_id' => $r->school_id ?? null,
                    'is_validated' => false,
                ]);
                $created[] = $deliberation;
            }
        }

        if (empty($created)) {
            return response()->json([
                'message' => 'Toutes les délibérations existent déjà pour cette classe et ce cours.',
            ], 409);
        }

        return DeliberationResource::collection(collect($created));
    }

    public function show($id)
    {
        $deliberation = Deliberation::with([
            'student',
            'cotations',
            'classroom',
            'filiaire',
            'academicLevel',
            'cycle',
            'schoolYear',
            'school',
            'course',
            'registration',
            'conduiteGrade', // Ajout ici aussi
        ])->findOrFail($id);

        return new DeliberationResource($deliberation);
    }

    public function update(DeliberationRequest $request, $id)
    {
        $deliberation = Deliberation::findOrFail($id);
        // On ne met à jour que le champ is_validated
        $deliberation->is_validated = $request->validated()['is_validated'] ?? $deliberation->is_validated;
        $deliberation->save();

        return new DeliberationResource($deliberation);
    }

    public function destroy($id)
    {
        $deliberation = Deliberation::findOrFail($id);
        $deliberation->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function initialize(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'course_id' => 'required|exists:courses,id',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        // Récupération des élèves inscrits dans cette classe et année
        $registrations = \App\Models\Registration::with(['student'])
            ->where('classroom_id', $request->classroom_id)
            ->where('school_year_id', $request->school_year_id)
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'message' => 'Aucun élève trouvé pour cette classe et cette année scolaire.',
            ], 404);
        }

        // Vérifier si une délibération existe déjà pour ces paramètres (pour chaque élève)
        $created = [];
        foreach ($registrations as $r) {
            $exists = Deliberation::where('classroom_id', $request->classroom_id)
                ->where('course_id', $request->course_id)
                ->where('school_year_id', $request->school_year_id)
                ->where('student_id', $r->student_id)
                ->exists();
            if (! $exists) {
                $deliberation = Deliberation::create([
                    'student_id' => $r->student_id,
                    'classroom_id' => $r->classroom_id,
                    'course_id' => $request->course_id,
                    'school_year_id' => $request->school_year_id,
                    'filiaire_id' => $r->filiaire_id ?? null,
                    'academic_level_id' => $r->academic_level_id ?? null,
                    'cycle_id' => $r->cycle_id ?? null,
                    'school_id' => $r->school_id ?? null,
                    'is_validated' => false,
                ]);
                $created[] = $deliberation;
            }
        }

        if (empty($created)) {
            return response()->json([
                'message' => 'Toutes les délibérations existent déjà pour cette classe et ce cours.',
            ], 409);
        }

        return DeliberationResource::collection(collect($created));
    }
}
