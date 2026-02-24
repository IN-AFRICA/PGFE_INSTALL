<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPersonal;
use Illuminate\Http\Request;

final class PersonnelWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = session('selected_school_id');
        $query = AcademicPersonal::query()
            ->with(['school:id,name'])
            ->latest('id')
            ->when($selectedSchoolId, fn ($q) => $q->where('school_id', $selectedSchoolId));

        if ($request->filled('school_id')) {
            $schoolId = (int) $request->integer('school_id');
            if (! $selectedSchoolId || $selectedSchoolId === $schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        $personnels = $query->paginate(20, ['id', 'name', 'pre_name', 'post_name', 'school_id'])
            ->appends($request->query());
        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => $selectedSchoolId ? AcademicPersonal::where('school_id', $selectedSchoolId)->count() : AcademicPersonal::count(),
        ];

        return view('backend.pages.personnels.index', compact('personnels', 'schools', 'stats'));
    }

    public function edit(AcademicPersonal $academic_personal)
    {
        $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);

        return view('backend.pages.personnels.edit', [
            'personnel' => $academic_personal,
            'schools' => $schools,
        ]);
    }

    public function update(Request $request, AcademicPersonal $academic_personal)
    {
        $data = $request->validate([
            'pre_name' => ['nullable', 'string', 'max:255'],
            'post_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);
        $academic_personal->update(array_filter($data, fn ($v) => $v !== null));

        return redirect()->route('admin.personnels.index')->with('success', 'Personnel mis à jour.');
    }

    public function destroy(AcademicPersonal $academic_personal)
    {
        $academic_personal->delete();

        return redirect()->route('admin.personnels.index')->with('success', 'Personnel supprimé.');
    }
}
