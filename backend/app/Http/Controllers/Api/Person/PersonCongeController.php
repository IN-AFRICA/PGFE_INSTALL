<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonCongeRequest;
use App\Models\PersonConge;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class PersonCongeController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Retrieving Person Congés with filters', $request->all());

            $query = PersonConge::with(['academicPersonal']);

            // Filtre par école: paramètre explicite ou école de l'utilisateur connecté
            $schoolId = $request->input('school_id', data_get(Auth::user(), 'school_id'));
            if (! empty($schoolId)) {
                $query->where('school_id', (int) $schoolId);
            }

            // Recherche texte sur la description du congé ou le personnel académique lié
            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhereHas('academicPersonal', function ($qa) use ($search) {
                            $qa->where('name', 'like', "%{$search}%")
                                ->orWhere('pre_name', 'like', "%{$search}%")
                                ->orWhere('post_name', 'like', "%{$search}%")
                                ->orWhere('matricule', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $perPage = (int) $request->input('per_page', 15);
            $conges = $query->latest('id')->paginate($perPage);

            return response()->json([
                'data' => $conges,
                'message' => 'Liste des congés récupérée avec succès.',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error retrieving Person Congés', ['error' => $exception->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des congés.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(PersonCongeRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $data['school_id'] = $user?->school_id;
            $data['author_id'] = $user?->id;
            $data['school_year_id'] = \App\Models\SchoolYear::where('is_active', true)->value('id');
            $personConge = PersonConge::create($data);
            Log::info('Person Conge created', ['id' => $personConge->id]);

            return response()->json([
                'data' => $personConge,
                'success' => true,
                'message' => 'Congé créé avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error creating Person Conge', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PersonConge $personConge)
    {
        try {
            Log::info('Retrieving Person Conge', ['id' => $personConge->id]);

            return response()->json([
                'data' => $personConge::findOrFail($personConge->id),
                'message' => 'Person conge retrieved successfully',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error retrieving Person Conge', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(PersonCongeRequest $request, PersonConge $personConge)
    {
        try {
            Log::info('Updating Person Conge', ['id' => $personConge->id]);
            $personConge->update($request->validated());

            return response()->json([
                'data' => $personConge,
                'success' => true,
                'message' => 'Congé mis à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error updating Person Conge', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PersonConge $personConge)
    {
        try {
            $personConge->delete();
            Log::info('Person Conge deleted', ['id' => $personConge->id]);

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error deleting Person Conge', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
