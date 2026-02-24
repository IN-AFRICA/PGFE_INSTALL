<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Infra;

use App\Http\Controllers\Controller;
use App\Models\InfraInfrastructure;
use App\Models\InfraEquipement;
use App\Models\InfraInfrastructureInventaire;
use App\Models\InfraInventaire;
use App\Models\InfraEtat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class InfraDashboardWebController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $totalInfrastructures = InfraInfrastructure::count();
        $totalEquipements = InfraEquipement::count();
        $totalInventaires = InfraInfrastructureInventaire::count() + InfraInventaire::count();
        $totalSignalements = InfraEtat::count();

        // Répartition des infrastructures par statut (basé sur le dernier inventaire)
        $infrastructuresStatus = InfraInfrastructureInventaire::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Derniers inventaires d'infrastructure
        $recentInventaires = InfraInfrastructureInventaire::with(['infrastructure'])
            ->latest()
            ->limit(5)
            ->get();

        // Derniers inventaires d'équipement
        $recentEquipementInventaires = InfraInventaire::with(['equipement'])
            ->latest()
            ->limit(5)
            ->get();

        // Derniers signalements d'état
        $recentEtats = InfraEtat::with(['infrastructure'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.infra.dashboard', compact(
            'totalInfrastructures',
            'totalEquipements',
            'totalInventaires',
            'totalSignalements',
            'infrastructuresStatus',
            'recentInventaires',
            'recentEquipementInventaires',
            'recentEtats'
        ));
    }
}
