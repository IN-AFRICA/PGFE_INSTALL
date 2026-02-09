<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
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
        
        $stats = [
            'total' => $selectedSchoolId ? Student::where('school_id', $selectedSchoolId)->count() : Student::count(),
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

        return view('backend.pages.students.edit', compact('student', 'schools'));
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
