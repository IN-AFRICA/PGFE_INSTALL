<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Currencies;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyRequest;
use App\Models\Currency;

final class CurrencyController extends Controller
{
    public function index()
    {
        $data = Currency::with('activeExchangeRate')->get();

        return response()->json(
            [
                'success' => true,
                'currencies' => $data,
            ],
            200
        );
    }

    public function store(CurrencyRequest $request)
    {
        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency = Currency::create($data);

        return response()->json([
            'success' => true,
            'currency' => $currency,
        ], 201);
    }

    public function show(Currency $currency)
    {
        if (! $currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        return response()->json([
            'success' => true,
            'currency' => $currency->load('exchangeRates'),
        ], 200);
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        if (! $currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        $data = $request->validated();

        if (isset($data['is_default']) && $data['is_default']) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($data);

        return response()->json([
            'success' => true,
            'currency' => $currency,
        ], 200);
    }

    public function destroy(Currency $currency)
    {
        if (! $currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        $currency->delete();

        return response()->json(['success' => true, 'message' => 'Currency deleted successfully'], 200);
    }
}
