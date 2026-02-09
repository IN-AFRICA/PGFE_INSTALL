<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

final class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::all();

        return response()->json([
            'success' => true,
            'methods' => $methods,
        ]);
    }

    public function store(PaymentMethodRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $method = PaymentMethod::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Méthode de paiement créée avec succès.',
            'method' => $method,
        ], 201);
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Détails de la méthode de paiement',
            'method' => $paymentMethod,
        ], 200);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $validated = $request->validated();

        $paymentMethod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Méthode de paiement mise à jour avec succès.',
            'method' => $paymentMethod,
        ]);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Méthode de paiement supprimée avec succès.',
        ]);
    }
}
