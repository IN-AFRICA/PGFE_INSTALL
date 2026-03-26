<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonAffectation;
use Illuminate\Http\Request;

final class PersonAffectationWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = session('selected_school_id');

        $query = PersonAffectation::query()
            ->with(['academicPersonal:id,name,pre_name,post_name,matricule', 'schoolYear:id,name', 'school:id,name'])
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

        $affectations = $query->paginate(25)->appends($request->query());

        return view('backend.pages.person-affectations.index', compact('affectations', 'selectedSchoolId'));
    }
}
