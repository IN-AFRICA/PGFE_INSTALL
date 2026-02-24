<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\RentalPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = RentalPayment::query()
            ->where('school_id', $user->school_id);

        if ($contractId = $request->query('rental_contract_id')) {
            $query->where('rental_contract_id', $contractId);
        }

        if ($method = $request->query('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('paid_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('paid_at', '<=', $to);
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100);

        $payments = $query->orderByDesc('paid_at')->paginate($perPage);

        return response()->json($payments);
    }

    public function store(PaymentRequest $request)
    {
        $payment = RentalPayment::create($request->validated());

        return response()->json(['data' => $payment], 201);
    }

    public function show(RentalPayment $payment)
    {
        return response()->json(['data' => $payment]);
    }

    public function update(PaymentRequest $request, RentalPayment $payment)
    {
        $payment->update($request->validated());

        return response()->json(['data' => $payment]);
    }

    public function destroy(RentalPayment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted']);
    }
}
