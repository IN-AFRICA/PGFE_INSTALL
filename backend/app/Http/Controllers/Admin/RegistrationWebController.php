<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicPersonal;
use App\Models\Classroom;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Type;
use Illuminate\Http\Request;

final class RegistrationWebController extends Controller
{
    public function index()
    {
        $registrations = Registration::query()
            ->with(['student:id,name,firstname,lastname', 'classroom:id,name', 'school:id,name'])
            ->latest('id')
            ->paginate(20, ['id', 'student_id', 'classroom_id', 'school_id', 'registration_date', 'registration_status']);

        return view('backend.pages.registrations.index', compact('registrations'));
    }

    public function create()
    {
        $students = Student::query()->orderByDesc('id')->limit(100)->get(['id', 'firstname', 'lastname', 'name']);
        $classrooms = Classroom::query()->orderBy('name')->get(['id', 'name']);
        $schoolYears = SchoolYear::query()->orderByDesc('id')->get(['id', 'title']);
        $personnels = AcademicPersonal::query()->orderByDesc('id')->limit(100)->get(['id', 'name', 'firstname', 'lastname']);
        $levels = AcademicLevel::query()->orderBy('name')->get(['id', 'name']);
        $types = Type::query()->orderBy('title')->get(['id', 'title']);

        return view('backend.pages.registrations.create', compact('students', 'classrooms', 'schoolYears', 'personnels', 'levels', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
                'student_id' => ['required', 'exists:students,id'],
                'school_id' => ['required', 'exists:schools,id'],
                'filiaire_id' => ['required', 'exists:filiaires,id'],
                'cycle_id' => ['required', 'exists:cycles,id'],
                'academic_level_id' => ['required', 'exists:academic_levels,id'],
                'classroom_id' => ['required', 'exists:classrooms,id'],
                'school_year_id' => ['required', 'exists:school_years,id'],
                'academic_personal_id' => ['required', 'exists:academic_personals,id'],
                'type_id' => ['required', 'exists:types,id'],
                'registration_date' => ['required', 'date'],
                'registration_status' => ['nullable', 'boolean'],
                'note' => ['nullable', 'string'],
        ]);

        // Contexte école automatique via AutoAssignsSchoolContext si activé
        $data['registration_status'] = (bool) ($data['registration_status'] ?? false);

        Registration::create($data);

        return redirect()->route('admin.registrations.index')->with('success', 'Inscription enregistrée.');
    }

    public function edit(Registration $registration)
    {
        $students = Student::query()->orderByDesc('id')->limit(100)->get(['id', 'firstname', 'lastname', 'name']);
        $classrooms = Classroom::query()->orderBy('name')->get(['id', 'name']);
        $schoolYears = SchoolYear::query()->orderByDesc('id')->get(['id', 'title']);
        $personnels = AcademicPersonal::query()->orderByDesc('id')->limit(100)->get(['id', 'name', 'firstname', 'lastname']);
        $levels = AcademicLevel::query()->orderBy('name')->get(['id', 'name']);
        $types = Type::query()->orderBy('title')->get(['id', 'title']);

        return view('backend.pages.registrations.edit', compact('registration', 'students', 'classrooms', 'schoolYears', 'personnels', 'levels', 'types'));
    }

    public function update(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'academic_personal_id' => ['required', 'exists:academic_personals,id'],
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            'type_id' => ['required', 'exists:types,id'],
            'registration_date' => ['required', 'date'],
            'registration_status' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);
        $data['registration_status'] = (bool) ($data['registration_status'] ?? false);
        $registration->update($data);

        return redirect()->route('admin.registrations.index')->with('success', 'Inscription mise à jour.');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return redirect()->route('admin.registrations.index')->with('success', 'Inscription supprimée.');
    }
}
