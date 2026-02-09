<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Expenses;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\Accounts\AccountTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        $expenses = Expense::with([
            'school',
            'user',
            'paymentMethod',
            'accountType',
            'currency',
            'exchangeRate',
        ])->latest()->get();

        return response()->json(['success' => true, 'data' => $expenses]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $expense = Expense::create($data);

            AccountTransactionService::handleCreate($expense);

            DB::commit();

            return response()->json(['success' => true, 'data' => $expense], 201);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la dépense.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $expense->load([
                'school',
                'user',
                'paymentMethod',
                'accountType',
                'currency',
                'exchangeRate',
            ]),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $expense->update($data);

            AccountTransactionService::handleUpdate($expense);

            DB::commit();

            return response()->json(['success' => true, 'data' => $expense]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Expense $expense): JsonResponse
    {
        DB::beginTransaction();

        try {
            $expense->delete();

            AccountTransactionService::handleDelete($expense);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Dépense supprimée.']);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
