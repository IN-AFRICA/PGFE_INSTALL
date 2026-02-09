<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ValidationAureat;

use App\Exports\ValidationAureatsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ExportController extends Controller
{
    public function export(Request $request)
    {
        $class = $request->input('class', $request->input('classe'));
        $min = (int) $request->input('min', 80);

        // Retourne un export Responsable (Maatwebsite) avec fileName interne
        return new ValidationAureatsExport(
            classFilter: $class,
            minPercentage: $min,
        );
    }

    public function exportAll(Request $request)
    {
        $class = $request->input('class', $request->input('classe'));
        if (empty($class)) {
            return response()->json([
                'success' => false,
                'message' => "Le paramètre 'class' (ou 'classe') est requis pour l'export de la classe.",
            ], 422);
        }

        return new ValidationAureatsExport(
            classFilter: $class,
            minPercentage: 0,
            includeNullPercentage: true,
        );
    }
}
