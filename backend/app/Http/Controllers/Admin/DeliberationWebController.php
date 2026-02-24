<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deliberation;
use Illuminate\Http\Request;

final class DeliberationWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = Deliberation::with(['student:id,firstname,lastname,name,matricule', 'classroom:id,name', 'course:id,label', 'schoolYear:id,name'])
            ->latest('id');

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        $deliberations = $query->paginate(25);

        return view('backend.pages.deliberations.index', compact('deliberations', 'selectedSchoolId'));
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
