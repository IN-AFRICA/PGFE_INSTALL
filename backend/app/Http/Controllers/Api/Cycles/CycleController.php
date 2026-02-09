<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Cycles;

use App\Exports\CyclesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CycleRequest;
use App\Models\Cycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

final class CycleController extends Controller
{
    /**
     * Export cycles as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'cycles_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new CyclesExport(), $fileName);
    }
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $query = Cycle::with(['filiaire', 'academicLevels.classrooms']);

        if ($user && $user->school_id) {
            $query->whereHas('filiaire', function ($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        }

        $cycles = $query
            ->orderBy('name')
            ->get()
            ->unique(function (Cycle $cycle) {
                return $cycle->name.'|'.$cycle->filiaire_id;
            })
            ->values();

        return response()->json([
            'data' => $cycles,
            'message' => 'Liste des cycles récupérée avec succès',
        ]);
    }

    public function store(CycleRequest $request): JsonResponse
    {
        $cycle = Cycle::create($request->validated());
        return response()->json([
            'data' => $cycle->load(['filiaire', 'academicLevels.classrooms']),
            'message' => 'Cycle créé avec succès',
        ], 201);
    }

    public function show(Cycle $cycle): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        return response()->json([
            'data' => $cycle->load(['filiaire', 'academicLevels.classrooms']),
            'message' => 'Cycle récupéré avec succès',
        ]);
    }

    public function update(CycleRequest $request, Cycle $cycle): JsonResponse
    {
        $cycle->update($request->validated());

        return response()->json([
            'data' => $cycle->fresh(['school', 'academicLevels.filiaires.classrooms']),
            'message' => 'Cycle mis à jour avec succès',
        ]);
    }

    public function destroy(Cycle $cycle): JsonResponse
    {
        $cycle->delete();

        return response()->json([
            'message' => 'Cycle supprimé avec succès',
        ]);
    }
}
