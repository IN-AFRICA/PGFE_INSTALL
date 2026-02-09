<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Filiaires;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFiliaireRequest;
use App\Models\Filiaire;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class UpdateFiliaireController extends Controller
{
    public function __invoke(Filiaire $filiaire, UpdateFiliaireRequest $request)
    {
        $validated = $request->validated();

        $filiaire->update($validated);

        Log::info($filiaire);

        return response()->json([
            'data' => $filiaire->load('cycles.academicLevels'),
            'message' => 'Filière mise à jour avec succès',
        ], Response::HTTP_ACCEPTED);
    }
}
