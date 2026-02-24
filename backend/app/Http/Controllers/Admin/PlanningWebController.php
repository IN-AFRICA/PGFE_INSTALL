<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\School;
use App\Models\SchoolworkPlanning;
use Illuminate\Http\Request;

final class PlanningWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = SchoolworkPlanning::query()
            ->with(['classroom:id,name', 'course:id,label', 'school:id,name'])
            ->latest('id');

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        if ($request->filled('school_id')) {
            $schoolId = (int) $request->integer('school_id');
            if (! $selectedSchoolId || $selectedSchoolId === $schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', (int) $request->integer('classroom_id'));
        }

        if ($search = mb_trim((string) $request->get('q', ''))) {
            $query->where('label', 'like', "%{$search}%");
        }

        $plannings = $query->paginate(20, ['id', 'label', 'classroom_id', 'course_id', 'school_id', 'start_date', 'end_date'])
            ->appends($request->query());

        $schools = School::orderBy('name')->get(['id', 'name']);
        $classrooms = Classroom::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => $selectedSchoolId ? SchoolworkPlanning::where('school_id', $selectedSchoolId)->count() : SchoolworkPlanning::count(),
        ];

        return view('backend.pages.planning.index', compact('plannings', 'schools', 'classrooms', 'stats'));
    }

    public function create(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $schools = School::orderBy('name')->get(['id', 'name']);
        $classrooms = Classroom::orderBy('name')->get(['id', 'name']);
        $courses = Course::orderBy('label')->get(['id', 'label']);

        return view('backend.pages.planning.create', compact('schools', 'classrooms', 'courses', 'selectedSchoolId'));
    }

    public function store(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];

        $data = $request->validate($rules);

        $user = $request->user();

        if (! $user || ! $user->academicPersonal) {
            return back()
                ->withInput()
                ->withErrors(['label' => 'Aucun personnel académique n\'est lié à votre compte. Impossible d\'enregistrer l\'auteur de la planification.']);
        }

        // L'école vient de l'utilisateur ou de l'école active (pas du formulaire)
        $schoolId = (int) ($user->school_id ?? $selectedSchoolId ?? 0);

        if (! $schoolId) {
            return back()
                ->withInput()
                ->withErrors(['label' => 'Aucune école active n\'est définie. Veuillez sélectionner une école dans le tableau de bord.']);
        }

        // Auteur: doit être un personnel académique lié à l'utilisateur
        $authorId = $user->academicPersonal->id;

        SchoolworkPlanning::create([
            'label' => $data['label'],
            'classroom_id' => (int) $data['classroom_id'],
            'course_id' => (int) $data['course_id'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'school_id' => $schoolId,
            'author_id' => $authorId,
        ]);

        return redirect()->route('admin.planning.index')->with('success', 'Planification créée avec succès.');
    }

    private function activeSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user && $user->hasRole('super-admin')) {
            return session('selected_school_id');
        }

        return $user?->school_id;
    }
}
