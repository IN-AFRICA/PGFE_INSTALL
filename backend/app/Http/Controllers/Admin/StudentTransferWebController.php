<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StudentTransferWebController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $student->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.students.index')->with('error', 'Cet élève n’appartient pas à l’école sélectionnée.');
        }

        $transfers = StudentTransfer::with(['fromSchool', 'toSchool', 'fromClassroom', 'toClassroom', 'schoolYear'])
            ->where('student_id', $student->id)
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();

        $schools = School::orderBy('name')->get(['id', 'name']);

        return view('backend.pages.students.transfers', compact('student', 'transfers', 'schools'));
    }

    public function store(Request $request, Student $student)
    {
        $selectedSchoolId = $this->activeSchoolId($request);
        if ($selectedSchoolId && (int) $student->school_id !== (int) $selectedSchoolId) {
            return redirect()->route('admin.students.index')->with('error', 'Cet élève n’appartient pas à l’école sélectionnée.');
        }

        $fromSchoolId = (int) $student->school_id;

        $data = $request->validate([
            'to_school_id' => 'required|integer|exists:schools,id',
            'school_year_id' => 'nullable|integer|exists:school_years,id',
            'transfer_date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        if ($data['to_school_id'] === $fromSchoolId) {
            return back()->with('error', "La nouvelle école doit être différente de l'école actuelle.");
        }

        DB::transaction(function () use ($data, $student, $fromSchoolId, $request) {
            StudentTransfer::create([
                'student_id' => $student->id,
                'from_school_id' => $fromSchoolId,
                'to_school_id' => $data['to_school_id'],
                'from_classroom_id' => null,
                'to_classroom_id' => null,
                'school_year_id' => $data['school_year_id'] ?? null,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $student->update([
                'school_id' => $data['to_school_id'],
            ]);
        });

        return redirect()
            ->route('admin.students.transfers.index', $student)
            ->with('success', 'Transfert effectué avec succès.');
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
