<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class ClassroomWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        $query = Classroom::query()
            ->with(['school:id,name', 'filiaire:id,name'])
            ->when($selectedSchoolId, fn ($q) => $q->where('school_id', $selectedSchoolId));

        if ($request->filled('school_id')) {
            // Forcer la cohérence: ignorer school_id s’il ne correspond pas à l’école active côté super admin
            $schoolId = (int) $request->integer('school_id');
            if (! $selectedSchoolId || $selectedSchoolId === $schoolId) {
                $query->where('school_id', $schoolId);
            }
        }
        if ($search = mb_trim((string) $request->get('q', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $classrooms = $query->orderByDesc('id')
            ->paginate(20, ['id', 'name', 'school_id', 'filiaire_id'])
            ->appends($request->query());

        $schools = \App\Models\School::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.classrooms.index', compact('classrooms', 'schools'));
    }

    public function create(Request $request)
    {
        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);
        $filiaires = \App\Models\Filiaire::orderBy('name')->get(['id', 'name']);

        return view('backend.pages.classrooms.create', compact('schools', 'filiaires'));
    }

    public function store(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('classrooms', 'name')->where(fn ($q) => $q->where('school_id', $selectedSchoolId)),
            ],
            'filiaire_id' => ['required', 'exists:filiaires,id'],
        ]);

        // Forcer l’école active, ignorer toute entrée school_id côté super admin
        if (! $selectedSchoolId) {
            return back()->with('error', "Impossible de déterminer l'école active.")->withInput();
        }

        $payload = [
            'name' => $data['name'],
            'school_id' => $selectedSchoolId,
            'filiaire_id' => $data['filiaire_id'],
        ];

        Classroom::create($payload);

        return redirect()->route('admin.classrooms.index')->with('success', 'Classe créée avec succès.');
    }

    public function edit(Request $request, Classroom $classroom)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $classroom->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.classrooms.index')->with('error', 'Cette classe n’appartient pas à l’école sélectionnée.');
        }

        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);
        $filiaires = \App\Models\Filiaire::orderBy('name')->get(['id', 'name']);

        return view('backend.pages.classrooms.edit', compact('classroom', 'schools', 'filiaires'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $classroom->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.classrooms.index')->with('error', 'Cette classe n’appartient pas à l’école sélectionnée.');
        }

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('classrooms', 'name')->where(fn ($q) => $q->where('school_id', $classroom->school_id))->ignore($classroom->id),
            ],
            'filiaire_id' => ['required', 'exists:filiaires,id'],
        ]);

        $classroom->update([
            'name' => $data['name'],
            'filiaire_id' => $data['filiaire_id'],
        ]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Classe mise à jour.');
    }

    public function destroy(Request $request, Classroom $classroom)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $classroom->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.classrooms.index')->with('error', 'Cette classe n’appartient pas à l’école sélectionnée.');
        }

        $classroom->delete();

        return redirect()->route('admin.classrooms.index')->with('success', 'Classe supprimée.');
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
