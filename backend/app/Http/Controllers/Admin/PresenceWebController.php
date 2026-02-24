<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;

final class PresenceWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedSchoolId = $this->activeSchoolId($request);

        $query = Presence::with(['student:id,firstname,lastname,name,matricule', 'classroom:id,name', 'school:id,name'])
            ->latest('created_at');

        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        }

        $presences = $query->paginate(25);

        return view('backend.pages.presences.index', compact('presences', 'selectedSchoolId'));
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
