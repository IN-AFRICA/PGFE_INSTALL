<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Presence;
use App\Models\Deliberation;
use App\Models\StudentExit;
use App\Models\Visit;
use App\Enums\GenderEnum;
use Illuminate\Http\Request;

final class StudentWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        $query = Student::query()
            ->with('school:id,name')
            ->latest('id')
            ->when($selectedSchoolId, fn ($q) => $q->where('school_id', $selectedSchoolId));

        if ($request->filled('school_id')) {
            $schoolId = (int) $request->integer('school_id');
            if (! $selectedSchoolId || $selectedSchoolId === $schoolId) {
                $query->where('school_id', $schoolId);
            }
        }
        if ($search = mb_trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20, ['id', 'firstname', 'lastname', 'name', 'gender', 'school_id', 'matricule'])
            ->appends($request->query());

        $schools = \App\Models\School::query()->orderBy('name')->get(['id', 'name']);

        $baseStatsQuery = Student::query();
        if ($selectedSchoolId) {
            $baseStatsQuery->where('school_id', $selectedSchoolId);
        }

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'boys' => (clone $baseStatsQuery)->where('gender', GenderEnum::MA->value)->count(),
            'girls' => (clone $baseStatsQuery)->where('gender', GenderEnum::FA->value)->count(),
        ];

        return view('backend.pages.students.index', compact('students', 'schools', 'stats'));
    }

    public function edit(Request $request, Student $student)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $student->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.students.index')->with('error', 'Cet élève n’appartient pas à l’école sélectionnée.');
        }

        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);

        // Charger le contexte scolaire et l'inscription principale
        $student->loadMissing(['school', 'registration.classroom', 'registration.schoolYear']);

        // Présences récentes de l'élève (limitées pour la vue super admin)
        $presences = Presence::query()
            ->where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Fiches de cotation récentes (notes)
        $notes = $student->notes()
            ->with(['course:id,name', 'semester:id,name', 'schoolYear:id,title', 'classroom:id,name'])
            ->latest('id')
            ->limit(20)
            ->get();

        // Délibérations récentes de l'élève
        $deliberations = Deliberation::query()
            ->with(['classroom:id,name', 'course:id,name', 'schoolYear:id,title'])
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        // Sorties récentes (StudentExit)
        $exits = StudentExit::query()
            ->with(['filiere:id,name', 'schoolYear:id,title'])
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        // Visites de classe liées à la même école (aperçu global, pas par élève)
        $visits = Visit::query()
            ->with(['classroom:id,name', 'school:id,name'])
            ->where('school_id', $student->school_id)
            ->latest('visit_hour')
            ->limit(20)
            ->get();

        return view('backend.pages.students.edit', compact('student', 'schools', 'presences', 'notes', 'deliberations', 'exits', 'visits'));
    }

    public function update(Request $request, Student $student)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $student->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.students.index')->with('error', 'Cet élève n’appartient pas à l’école sélectionnée.');
        }

        $data = $request->validate([
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:10'],
            // Ne pas permettre de changer d’école ici pour éviter les incohérences
        ]);

        $student->update(array_filter($data, fn ($v) => $v !== null));

        return redirect()->route('admin.students.index')->with('success', 'Élève mis à jour.');
    }

    public function destroy(Request $request, Student $student)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $student->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.students.index')->with('error', 'Cet élève n’appartient pas à l’école sélectionnée.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Élève supprimé.');
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
