<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonAffectationRequest;
use App\Http\Resources\PersonAffectationResource;
use App\Models\PersonAffectation;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PersonAffectationController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonAffectation::query()
            ->with(['academicPersonal', 'schoolYear']);

        // Recherche sur le nom du personnel ou le lieu d'affectation
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim((string) $request->input('search')));

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(lieu_affectation) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('academicPersonal', function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(post_name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(pre_name) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $paginator = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => PersonAffectationResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'message' => 'Person affectations retrieved successfully',
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function store(PersonAffectationRequest $request): PersonAffectationResource|\Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $data['school_id'] = $user?->school_id;
            $data['author_id'] = $user?->id;
            $data['school_year_id'] = \App\Models\SchoolYear::where('is_active', true)->value('id');
            $personAffectation = PersonAffectation::create($data);
            Log::info('Person Affectation created', ['id' => $personAffectation->id]);

            return response()->json([
                'data' => (new PersonAffectationResource($personAffectation->load(['academicPersonal', 'schoolYear']))),
                'success' => true,
                'message' => 'Affectation créée avec succès.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error creating Person Affectation', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PersonAffectation $personAffectation)
    {
        try {
            Log::info('Retrieving Person Affectation', ['id' => $personAffectation->id]);

            $personAffectation = $personAffectation::with(['academicPersonal', 'schoolYear'])->findOrFail($personAffectation->id);

            return response()->json([
                'data' => new PersonAffectationResource($personAffectation),
                'message' => 'Person affectation retrieved successfully',
                'success' => true,
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error retrieving Person Affectation', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(PersonAffectationRequest $request, PersonAffectation $personAffectation)
    {
        try {
            Log::info('Updating Person Affectation', ['id' => $personAffectation->id]);
            $personAffectation->update($request->validated());

            return response()->json([
                'data' => new PersonAffectationResource($personAffectation->load(['academicPersonal', 'schoolYear'])),
                'success' => true,
                'message' => 'Affectation mise à jour avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error updating Person Affectation', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(PersonAffectation $personAffectation): \Illuminate\Http\JsonResponse
    {
        try {
            $personAffectation->delete();
            Log::info('Person Affectation deleted', ['id' => $personAffectation->id]);

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);
            Log::error('Error deleting Person Affectation', ['error' => $exception->getMessage()]);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
