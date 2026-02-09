<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Types;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountTypeRequest;
use App\Models\AccountType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AccountTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountType::query()->with(['accountNumber', 'school', 'academicPersonal']);

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        $accountTypes = $query->paginate();

        return response()->json([
            'data' => $accountTypes,
            'message' => 'Account types retrieved successfully.',
        ]);
    }

    public function store(AccountTypeRequest $request): JsonResponse
    {
        $accountType = AccountType::create($request->validated());

        return response()->json([
            'data' => $accountType->load(['accountNumber', 'school', 'academicPersonal']),
            'message' => 'Account type created successfully.',
        ], Response::HTTP_CREATED);
    }

    public function show(AccountType $accountType)
    {
        return \response()->json([
            'data' => $accountType->load(['accountNumber', 'school', 'academicPersonal']),
            'message' => 'Account type retrieved successfully.',
        ]);
    }

    public function update(AccountTypeRequest $request, AccountType $accountType): JsonResponse
    {
        $accountType->update($request->validated());

        return response()->json([
            'data' => $accountType->load(['accountNumber', 'school', 'academicPersonal']),
            'message' => 'Account type updated successfully.',
        ]);
    }

    public function destroy(AccountType $accountType): JsonResponse
    {
        $accountType->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
