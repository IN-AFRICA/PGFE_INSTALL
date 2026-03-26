<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonPresence;
use Illuminate\Http\Request;

final class PersonnelPresenceWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = session('selected_school_id');

        $query = PersonPresence::query()
            ->with(['personnel:id,name,pre_name,post_name,matricule', 'school:id,name'])
            ->latest('created_at');

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        if ($request->filled('school_id')) {
            $schoolId = (int) $request->integer('school_id');
            if (! $selectedSchoolId || $selectedSchoolId === $schoolId) {
                $query->where('school_id', $schoolId);
            }
        }

        $personPresences = $query->paginate(25)->appends($request->query());

        return view('backend.pages.personnel-presences.index', compact('personPresences', 'selectedSchoolId'));
    }
}
