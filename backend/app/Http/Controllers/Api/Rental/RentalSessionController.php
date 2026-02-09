<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Models\RentalSession;
use Illuminate\Http\Request;

class RentalSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = RentalSession::where('school_id', $request->user()->school_id)
            ->with(['equipment', 'client']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($equipmentId = $request->query('equipment_id')) {
            $query->where('equipment_id', $equipmentId);
        }

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('session_code', 'like', "%{$search}%")
                    ->orWhereHas('equipment', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('equipment_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($cl) use ($search) {
                        $cl->where('name', 'like', "%{$search}%")
                            ->orWhere('tenant_code', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100);

        $sessions = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $session = RentalSession::create($data);

        return response()->json(['data' => $session], 201);
    }

    public function show(RentalSession $session)
    {
        $this->authorize('view', $session);
        return response()->json(['data' => $session->load(['equipment', 'client'])]);
    }

    public function update(Request $request, RentalSession $session)
    {
        $this->authorize('update', $session);

        $data = $request->validate([
            'status' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
        ]);

        $session->update($data);

        return response()->json(['data' => $session]);
    }

    public function destroy(RentalSession $session)
    {
        $this->authorize('delete', $session);
        $session->delete();
        return response()->json(['message' => 'Session deleted']);
    }
}
