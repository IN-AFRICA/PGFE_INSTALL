<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\RentalContractRequest;
use App\Models\RentalContract;
use Illuminate\Http\Request;

class RentalContractController extends Controller
{
    public function index(Request $request)
    {
        $query = RentalContract::where('school_id', $request->user()->school_id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('contract_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($projectId = $request->query('project_id')) {
            $query->where('project_id', $projectId);
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100);

        $contracts = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($contracts);
    }

    public function store(RentalContractRequest $request)
    {
        $contract = RentalContract::create($request->validated());
        return response()->json(['data' => $contract], 201);
    }

    public function show(RentalContract $contract)
    {
        $this->authorize('view', $contract);
        return response()->json(['data' => $contract]);
    }

    public function update(RentalContractRequest $request, RentalContract $contract)
    {
        $this->authorize('update', $contract);
        $contract->update($request->validated());
        return response()->json(['data' => $contract]);
    }

    public function destroy(RentalContract $contract)
    {
        $this->authorize('delete', $contract);
        $contract->update(['status' => 'inactive']);
        return response()->json(['message' => 'Contract disabled']);
    }
}
