<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeeRequest;
use App\Models\Fee;
use Illuminate\Http\JsonResponse;

    use \Maatwebsite\Excel\Facades\Excel;
    use App\Exports\FeesExport;

final class FeeController extends Controller
{
    public function index(): JsonResponse
    {
        $fees = Fee::with(['currency', 'feeType', 'school'])->get();

        return response()->json([
            'success' => true,
            'fees' => $fees,
        ]);
    }

    public function store(FeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        // school_id n’est pas fourni par l’API: il sera auto-assigné par le modèle (AutoAssignsSchoolContext)
        $fee = Fee::create($data);

        return response()->json([
            'success' => true,
            'fee' => $fee->load(['currency', 'feeType', 'school']),
        ], 201);
    }

    public function update(FeeRequest $request, Fee $fee): JsonResponse
    {
        $data = $request->validated();
        $fee->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Frais mis à jour avec succès.',
            'fee' => $fee->load(['currency', 'feeType', 'school']),
        ], 200);
    }

    public function show(Fee $fee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'fee' => $fee->load(['currency', 'feeType', 'school']),
        ]);
    }

    public function destroy(Fee $fee): JsonResponse
    {
        $fee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee deleted successfully.',
        ]);
    }
    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\FeesExport, 'frais.xlsx');
    }
}
