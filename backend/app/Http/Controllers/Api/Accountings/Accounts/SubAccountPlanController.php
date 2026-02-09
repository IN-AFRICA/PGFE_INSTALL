<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\Accounts\SubAccountPlanRequest;
use App\Models\Journal;
use App\Models\SubAccountPlan;
use App\Exports\SubAccountPlanExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SubAccountPlanController extends Controller
{
    /**
     * Export SubAccountPlan as Excel
     */
    public function export()
    {
        return Excel::download(new SubAccountPlanExport, 'sub_account_plans.xlsx');
    }

    /**
     * Export SubAccountPlan as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = SubAccountPlan::with('accountPlan');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->get('search')}%");
        }
        $subAccountPlans = $query->get();

        $html = view('exports.sub_account_plans', [
            'subAccountPlans' => $subAccountPlans
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'sub_account_plans_' . now()->format('Ymd_His') . '.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
    /**
     * Affiche la liste paginée des sous-comptes
     */
    public function index(Request $request): JsonResponse
    {

        try {
            $query = SubAccountPlan::with('accountPlan');

            $search = strtolower(trim((string) $request->input('search', '')));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            }

            $subAccounts = $query->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Liste des sous-comptes récupérée avec succès.',
                'data' => $subAccounts,
                'meta' => [
                    'current_page' => $subAccounts->currentPage(),
                    'last_page' => $subAccounts->lastPage(),
                    'per_page' => $subAccounts->perPage(),
                    'total' => $subAccounts->total(),
                    'next_page_url' => $subAccounts->nextPageUrl(),
                    'prev_page_url' => $subAccounts->previousPageUrl(),
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crée un nouveau sous-compte
     */
    public function store(SubAccountPlanRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $subAccount = SubAccountPlan::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'account_plan_id' => $validated['account_plan_id'], // correspondance logique
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sous-compte créé avec succès.',
                'data' => $subAccount,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Affiche un sous-compte spécifique
     */
    public function show(SubAccountPlan $subAccountPlan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Sous-compte récupéré avec succès.',
            'data' => $subAccountPlan->load('accountPlan'),
        ], Response::HTTP_OK);
    }

    /**
     * Met à jour un sous-compte existant
     */
    public function update(SubAccountPlanRequest $request, SubAccountPlan $subAccountPlan): JsonResponse
    {
        try {
            $validated = $request->validated();

            $subAccountPlan->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'account_plan_id' => $validated['account_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sous-compte mis à jour avec succès.',
                'data' => $subAccountPlan,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime un sous-compte
     */
    public function destroy(SubAccountPlan $subAccountPlan): JsonResponse
    {
        try {
            $subAccountPlan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sous-compte supprimé avec succès.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
