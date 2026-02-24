<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Infrastructure;

use App\Http\Controllers\Controller;
use App\Models\InfraInfrastructure;
use App\Models\InfraEquipement;
use App\Models\InfraInfrastructureInventaire;
use App\Models\InfraInventaire;
use App\Models\InfraEtat;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class InfraDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $stats = [
                'total_infrastructures' => InfraInfrastructure::count(),
                'total_equipements' => InfraEquipement::count(),
                'total_inventaires' => InfraInfrastructureInventaire::count() + InfraInventaire::count(),
                'total_signalements' => InfraEtat::count(),
                'status_distribution' => InfraInfrastructureInventaire::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get(),
                'latest_inventaires' => InfraInfrastructureInventaire::with(['infrastructure'])
                    ->latest()
                    ->limit(5)
                    ->get(),
                'latest_etats' => InfraEtat::with(['infrastructure'])
                    ->latest()
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
