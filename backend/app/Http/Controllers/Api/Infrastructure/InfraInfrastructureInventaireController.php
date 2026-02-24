<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfraInfrastructureInventaireRequest;
use App\Http\Resources\InfraInfrastructureInventaireResource;
use App\Models\InfraInfrastructureInventaire;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class InfraInfrastructureInventaireController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $inventaires = InfraInfrastructureInventaire::with('infrastructure')->latest()->get();

            return response()->json([
                'data' => InfraInfrastructureInventaireResource::collection($inventaires),
                'success' => true,
                'message' => 'Liste des inventaires des infrastructures récupérée avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Une erreur est survenue.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(InfraInfrastructureInventaireRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user || ! $user->school_id) {
                return response()->json([
                    'error' => "Aucune école active n'a été trouvée pour cet utilisateur.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $request->validated();

            // Forcer le contexte établissement / auteur comme pour les autres ressources infra
            $data['school_id'] = $user->school_id;
            $data['author_id'] = $user->id;

            $inventaire = InfraInfrastructureInventaire::create($data);
            $inventaire->load('infrastructure');

            return response()->json([
                'data' => new InfraInfrastructureInventaireResource($inventaire),
                'success' => true,
                'message' => 'Inventaire de l\'infrastructure créé avec succès',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Une erreur est survenue.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(InfraInfrastructureInventaire $infraInfrastructureInventaire): \Illuminate\Http\JsonResponse
    {
        try {
            $infraInfrastructureInventaire->load([
                'infrastructure.categorie',
                'infrastructure.bailleur',
                'infrastructure.etats',
            ]);
            return response()->json([
                'data' => new InfraInfrastructureInventaireResource($infraInfrastructureInventaire),
                'success' => true,
                'message' => 'Inventaire de l\'infrastructure récupéré avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Une erreur est survenue.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(InfraInfrastructureInventaireRequest $request, InfraInfrastructureInventaire $infraInfrastructureInventaire): \Illuminate\Http\JsonResponse
    {
        try {
            $infraInfrastructureInventaire->update($request->validated());

            $infraInfrastructureInventaire->load('infrastructure');

            return response()->json([
                'data' => new InfraInfrastructureInventaireResource($infraInfrastructureInventaire),
                'success' => true,
                'message' => 'Inventaire de l\'infrastructure mis à jour avec succès',
            ]);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Une erreur est survenue.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(InfraInfrastructureInventaire $infraInfrastructureInventaire): \Illuminate\Http\JsonResponse
    {
        try {
            $infraInfrastructureInventaire->delete();

            return response()->json([
                'message' => 'Supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Une erreur est survenue.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
