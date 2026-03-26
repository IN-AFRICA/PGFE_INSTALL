<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Filiaires;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFiliaireRequest;
use App\Models\AcademicLevel;
use App\Models\Cycle;
use App\Models\Filiaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class StoreFiliaireController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(StoreFiliaireRequest $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user?->school_id;

        if (! $schoolId) {
            return response()->json([
                'message' => "Impossible de déterminer l'école de l'utilisateur connecté.",
                'success' => false,
            ], 422);
        }

        $filiaire = DB::transaction(function () use ($request, $schoolId) {

            $filiaire = Filiaire::create([
                'school_id' => $schoolId,
                'name' => $request->name,
                'code' => $request->code,
            ]);

            $cycles = [
                'Long' => ['1er', '2nd', '3eme', '4eme', '5eme', '6eme'],
                'Court' => ['1er', '2n', '3em', '4eme'],
            ];

            foreach ($cycles as $cycleName => $levels) {

                $cycle = $filiaire->cycles()->create([
                    'school_id' => $filiaire->school_id,
                    'name' => $cycleName,
                ]);

                foreach ($levels as $levelName) {

                    $cycle->academicLevels()->create([
                        'school_id' => $filiaire->school_id,
                        'name' => $levelName,
                    ]);
                }
            }

            return $filiaire;
        });

        return response()->json([
            'data' => $filiaire->load('cycles.academicLevels'),
            'message' => 'Filière créée avec succès',
        ], 201);
    }
}
