<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Personals;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalRequest;
use App\Models\AcademicPersonal;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PersonalController extends Controller
{
    public function index(Request $request)
    {
        // On utilise AcademicPersonal et on charge la fonction liée
        $query = AcademicPersonal::query()->with('fonction');

        // Filtres optionnels
        if ($request->filled('id') && is_numeric($request->input('id'))) {
            $query->where('id', (int) $request->input('id'));
        }
        if ($request->filled('school_id') && is_numeric($request->input('school_id'))) {
            $query->where('school_id', (int) $request->input('school_id'));
        }
        if ($request->filled('fonction_id') && is_numeric($request->input('fonction_id'))) {
            $query->where('fonction_id', (int) $request->input('fonction_id'));
        }
        if ($request->filled('type_id') && is_numeric($request->input('type_id'))) {
            $query->where('type_id', (int) $request->input('type_id'));
        }
        if ($request->filled('academic_level_id') && is_numeric($request->input('academic_level_id'))) {
            $query->where('academic_level_id', (int) $request->input('academic_level_id'));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('post_name', 'like', "%{$search}%")
                  ->orWhere('pre_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $data = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $data,
            'message' => 'Listes de personnels',
            'success' => true,
        ]);
    }

    public function store(PersonalRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        if (! isset($data['school_id']) && $user && property_exists($user, 'school_id')) {
            $data['school_id'] = $user->school_id;
        }
        $personal = Personal::create($data);

        return response()->json($personal, 201);
    }

    public function show($id)
    {
        $personal = Personal::with([
            'school', 'type', 'country', 'province', 'territory', 'commune',
            'father', 'mother', 'academicLevel', 'fonction', 'mechanisation',
        ])->findOrFail($id);

        return response()->json($personal);
    }

    public function update(PersonalRequest $request, $id)
    {
        $personal = Personal::findOrFail($id);
        $personal->update($request->validated());

        return response()->json($personal);
    }

    public function destroy($id)
    {
        $personal = Personal::findOrFail($id);
        $personal->delete();

        return response()->json(null, 204);
    }
}
