<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FeeTypes;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeeTypeRequest;
use App\Models\FeeType;

final class FeeTypeController extends Controller
{
    public function index()
    {
        $feeTypes = FeeType::all();

        return response()->json([
            'success' => true,
            'message' => 'Liste des types de frais',
            'fee_types' => $feeTypes,
        ], 200);
    }

    public function store(FeeTypeRequest $request)
    {
        $validated = $request->validated();

        $feeType = FeeType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Type de frais créé avec succès',
            'fee_type' => $feeType,
        ], 201);
    }

    public function show(int $feeType)
    {
        $feetypes = FeeType::findOrFail($feeType);

        return response()->json([
            'success' => true,
            'message' => 'Détails du type de frais',
            'fee_type' => $feetypes,
        ], 200);
    }

    public function update(FeeTypeRequest $request, FeeType $feeType)
    {
        $validated = $request->validated();

        $feeType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Type de frais mis à jour avec succès',
            'fee_type' => $feeType,
        ], 200);
    }

    public function destroy(int $feeType)
    {
        $feetypes = FeeType::findOrFail($feeType);
        $feetypes->delete()->onDelete();

        return response()->json([
            'success' => true,
            'message' => 'Type de frais supprimé avec succès',
        ], 200);
    }
}
