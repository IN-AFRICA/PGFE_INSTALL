<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolActivity;
use Illuminate\Http\Request;

final class SchoolActivityWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = SchoolActivity::query()
            ->with(['school:id,name', 'author:id,name'])
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

        if ($search = mb_trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate(20, ['id', 'label', 'place', 'quantity', 'start_date', 'end_date', 'school_id', 'author_id'])
            ->appends($request->query());

        $schools = School::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => $selectedSchoolId ? SchoolActivity::where('school_id', $selectedSchoolId)->count() : SchoolActivity::count(),
        ];

        return view('backend.pages.activities.index', compact('activities', 'schools', 'stats'));
    }

    public function create(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        $schools = School::orderBy('name')->get(['id', 'name']);

        return view('backend.pages.activities.create', compact('schools', 'selectedSchoolId'));
    }

    public function store(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'place' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];

        $data = $request->validate($rules);
        $user = $request->user();

        if (! $user || ! $user->academicPersonal) {
            return back()
                ->withInput()
                ->withErrors(['label' => 'Aucun personnel académique n\'est lié à votre compte. Impossible d\'enregistrer l\'auteur de l\'activité.']);
        }

        // L'école vient de l'utilisateur ou de l'école active (pas du formulaire)
        $schoolId = (int) ($user->school_id ?? $selectedSchoolId ?? 0);

        if (! $schoolId) {
            return back()
                ->withInput()
                ->withErrors(['label' => 'Aucune école active n\'est définie. Veuillez sélectionner une école dans le tableau de bord.']);
        }

        SchoolActivity::create([
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'place' => $data['place'] ?? null,
            'quantity' => $data['quantity'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'school_id' => $schoolId,
            'author_id' => $user->academicPersonal->id,
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activité créée avec succès.');
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
