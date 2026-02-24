<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraBailleurRequest;
use App\Http\Resources\InfraBailleurResource;
use App\Models\InfraBailleur;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraBailleurController extends Controller
{
    public function index(): Response|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return response()->json([
            'data' => InfraBailleurResource::collection(InfraBailleur::latest()->paginate(10)),
            'success' => true,
            'message' => 'Liste des bailleurs d\'infrastructures récupérée avec succès',
        ]);
    }

    public function store(InfraBailleurRequest $request): InfraBailleurResource|\Illuminate\Http\JsonResponse|Response
    {
        try {
            $user = $request->user();

            if (! $user || is_null($user->school_id)) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucune école active n'est associée à l'utilisateur connecté.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $user->relationLoaded('academicPersonal')) {
                $user->load('academicPersonal');
            }

            $data = $request->validated();
            $data['school_id'] = $user->school_id;
            
            if ($user->academicPersonal) {
                $data['academic_personal_id'] = $user->academicPersonal->id;
            }

            $infraBailleur = InfraBailleur::create($data);

            return response()->json([
                'data' => new InfraBailleurResource($infraBailleur),
                'success' => true,
                'message' => 'Bailleur d\'infrastructure créé avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraBailleur $bailleur): Response
    {
        return response()->json([
            'data' => new InfraBailleurResource($bailleur),
            'success' => true,
            'message' => 'Détails du bailleur d\'infrastructure récupérés avec succès',
        ], Response::HTTP_OK);
    }

    public function update(InfraBailleurRequest $request, InfraBailleur $bailleur): Response
    {
        try {
            $bailleur->update($request->validated());

            return response()->json([
                'data' => new InfraBailleurResource($bailleur),
                'success' => true,
                'message' => 'Bailleur d\'infrastructure mis à jour avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Une erreur est survenue.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraBailleur $bailleur): Response
    {
        try {
            $bailleur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bailleur supprimé avec succès'
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'Une erreur est survenue.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
