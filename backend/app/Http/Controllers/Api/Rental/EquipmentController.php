<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::where('school_id', $request->user()->school_id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('equipment_code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('mark_model', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if (! is_null($request->query('is_available'))) {
            $isAvailable = filter_var($request->query('is_available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (! is_null($isAvailable)) {
                $query->where('is_available', $isAvailable);
            }
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100);

        $equipments = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($equipments);
    }

    public function store(EquipmentRequest $request)
    {
        $equipment = Equipment::create($request->validated());
        return response()->json(['data' => $equipment], 201);
    }

    public function show(Equipment $equipment)
    {
        $this->authorize('view', $equipment);
        return response()->json(['data' => $equipment]);
    }

    public function update(EquipmentRequest $request, Equipment $equipment)
    {
        $this->authorize('update', $equipment);
        $equipment->update($request->validated());
        return response()->json(['data' => $equipment]);
    }

    public function destroy(Equipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $equipment->update(['status' => 'inactive']);
        return response()->json(['message' => 'Equipment disabled']);
    }
}
