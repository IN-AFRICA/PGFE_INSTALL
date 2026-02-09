<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ExchangeRates;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExchangeRateRequest;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;

    use \Maatwebsite\Excel\Facades\Excel;
    use App\Exports\ExchangeRatesExport;

final class ExchangeRateController extends Controller
{
    public function index()
    {
        $schoolId = data_get(Auth::user(), 'school_id');
        $query = ExchangeRate::with('currency');

        if (! empty($schoolId)) {
            $query->where('school_id', $schoolId);
        }

        return response()->json([
            'success' => true,
            'exchange_rates' => $query->get(),
        ]);
    }

    public function store(ExchangeRateRequest $request)
    {
        $schoolId = data_get(Auth::user(), 'school_id');
        if (empty($schoolId)) {
            return response()->json(['error' => 'Aucune école associée à votre compte.'], 422);
        }

        $data = $request->validatedWithSchool();

        if ($data['is_active'] ?? false) {
            ExchangeRate::where('currency_id', $data['currency_id'])
                ->where('school_id', $schoolId)
                ->update(['is_active' => false]);
        }

        $rate = ExchangeRate::create($data);

        return response()->json([
            'success' => true,
            'exchange_rate' => $rate,
        ], 201);
    }

    public function update(ExchangeRateRequest $request, ExchangeRate $exchangeRate)
    {
        $schoolId = data_get(Auth::user(), 'school_id');
        if ((int) $exchangeRate->school_id !== (int) $schoolId) {
            return response()->json(['error' => 'Exchange rate not found'], 404);
        }

        $data = $request->validatedWithSchool();

        if ($data['is_active'] ?? false) {
            ExchangeRate::where('currency_id', $data['currency_id'])
                ->where('school_id', $schoolId)
                ->update(['is_active' => false]);
        }

        $exchangeRate->update($data);

        return response()->json([
            'success' => true,
            'exchange_rate' => $exchangeRate,
        ]);
    }

    public function show(ExchangeRate $exchangeRate)
    {
        $schoolId = data_get(Auth::user(), 'school_id');
        if ((int) $exchangeRate->school_id !== (int) $schoolId) {
            return response()->json(['error' => 'Exchange rate not found'], 404);
        }

        return response()->json([
            'success' => true,
            'exchange_rate' => $exchangeRate->load('currency'),
        ]);
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $schoolId = data_get(Auth::user(), 'school_id');
        if ((int) $exchangeRate->school_id !== (int) $schoolId) {
            return response()->json(['error' => 'Exchange rate not found'], 404);
        }

        $exchangeRate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exchange rate deleted successfully',
        ]);
    }
        public function exportExcel()
{
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ExchangeRatesExport, 'taux_de_change.xlsx');
    }
}
