<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentExit;
use Illuminate\Http\Request;

final class StudentExitWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = StudentExit::with(['student:id,firstname,lastname,name,matricule', 'filiere:id,name', 'schoolYear:id,title'])
            ->latest('date')
            ->latest('id');

        if ($selectedSchoolId) {
            $query->whereHas('student', function ($q) use ($selectedSchoolId) {
                $q->where('school_id', $selectedSchoolId);
            });
        }

        $studentExits = $query->paginate(25);

        return view('backend.pages.student-exits.index', compact('studentExits', 'selectedSchoolId'));
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
