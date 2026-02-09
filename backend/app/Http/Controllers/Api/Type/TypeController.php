<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;
use App\Http\Requests\TypeRequest;
use App\Models\Type;
use Illuminate\Http\JsonResponse;

final class TypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = Type::latest()->get();

        return response()->json([
            'data' => $types,
            'message' => 'Liste des types récupérée avec succès',
        ]);
    }

    public function store(TypeRequest $request): JsonResponse
    {
        $type = Type::create($request->validated());

        return response()->json([
            'data' => $type,
            'message' => 'Type créé avec succès',
        ], 201);
    }

    public function update(TypeRequest $request, Type $type): JsonResponse
    {
        $type->update($request->validated());

        return response()->json([
            'data' => $type->fresh(),
            'message' => 'Type mis à jour avec succès',
        ]);
    }

    public function destroy(Type $type): JsonResponse
    {
        $type->delete();

        return response()->json([
            'message' => 'Type supprimé avec succès',
        ]);
    }
}
