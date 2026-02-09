<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\AccountNumber\StoreAccountNumber;
use App\Http\Requests\Accountings\AccountNumber\UpdateAccountNumber;
use App\Models\AccountNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AccountNumberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = AccountNumber::with('account')->latest();
        if ($user && ! $user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(label) LIKE ?', ["%{$search}%"]);
            });
        }

        $accounts = $query->get();

        return response()->json([
            'data' => $accounts,
            'message' => 'Account numbers retrieved successfully',
        ]);
    }

    public function store(StoreAccountNumber $request): JsonResponse
    {
        $validated = $request->validated();

        $accountNumber = AccountNumber::create($validated);

        return response()->json([
            'data' => $accountNumber->load('account'),
            'message' => 'Account number created successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateAccountNumber $request, AccountNumber $accountNumber): JsonResponse
    {
        $validated = $request->validated();

        $accountNumber->update($validated);

        return response()->json([
            'data' => $accountNumber->load('account'),
            'message' => 'Account number updated successfully',
        ]);
    }

    public function destroy(AccountNumber $accountNumber): JsonResponse
    {
        $accountNumber->delete();

        return response()->json([
            'message' => 'Account number deleted successfully',
        ]);
    }
}
