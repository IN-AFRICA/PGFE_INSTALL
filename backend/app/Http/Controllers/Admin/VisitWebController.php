<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;

final class VisitWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = Visit::with(['classroom:id,name', 'school:id,name'])
            ->latest('visit_hour');

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        $visits = $query->paginate(25);

        return view('backend.pages.visits.index', compact('visits', 'selectedSchoolId'));
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
