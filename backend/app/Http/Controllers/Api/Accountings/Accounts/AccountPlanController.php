<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\Accounts\AccountPlanRequest;
use App\Models\AccountPlan;
use App\Models\Journal;
use App\Exports\AccountPlanExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AccountPlanController extends Controller
{
    /**
     * Export AccountPlan as Excel
     */
    public function export()
    {
        return Excel::download(new AccountPlanExport, 'account_plans.xlsx');
    }

    /**
     * Export AccountPlan as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = AccountPlan::query();
        $user = $request->user();
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        $accountPlans = $query->get();

        $html = view('exports.account_plans', [
            'accountPlans' => $accountPlans
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'account_plans_' . now()->format('Ymd_His') . '.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = AccountPlan::query()->with(['classComptability', 'categoryComptability']);
        // Filtrer par école sauf pour super-admin
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }
        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }
        $accountPlans = $query->latest()->paginate(10);
        if ($accountPlans->isEmpty()) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Aucun plan comptable disponible.',
            ], Response::HTTP_OK);
        }

        $data = $accountPlans->getCollection()->map(function (AccountPlan $plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'class_comptability_id' => $plan->class_comptability_id,
                'category_comptability_id' => $plan->category_comptability_id,
                'user_id' => $plan->user_id,
                'school_id' => $plan->school_id,
                'created_at' => $plan->created_at,
                'updated_at' => $plan->updated_at,
                'class_comptability' => [
                    'name' => optional($plan->classComptability)->name,
                ],
                'category_comptability' => [
                    'name' => optional($plan->categoryComptability)->name,
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Liste des plans comptables récupérée avec succès.',
            'data' => $data,
            'meta' => [
                'current_page' => $accountPlans->currentPage(),
                'last_page' => $accountPlans->lastPage(),
                'per_page' => $accountPlans->perPage(),
                'total' => $accountPlans->total(),
                'next_page_url' => $accountPlans->nextPageUrl(),
                'prev_page_url' => $accountPlans->previousPageUrl(),
            ],
        ], Response::HTTP_OK);
    }

    public function store(AccountPlanRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;
            $accountPlan = AccountPlan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Plan comptable créé avec succès.',
                'data' => $accountPlan,
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(AccountPlan $accountPlan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Détails du plan comptable récupérés avec succès.',
            'data' => $accountPlan,
        ], Response::HTTP_OK);
    }

    public function update(AccountPlanRequest $request, AccountPlan $accountPlan): JsonResponse
    {
        try {
            $accountPlan->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Plan comptable mis à jour avec succès.',
                'data' => $accountPlan,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(AccountPlan $accountPlan): JsonResponse
    {
        try {
            $accountPlan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan comptable supprimé avec succès.',
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // ...existing code...
}
