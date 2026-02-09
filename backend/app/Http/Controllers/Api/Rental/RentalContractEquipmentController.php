<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\RentalContractEquipmentRequest;
use App\Models\RentalContractEquipment;
use Illuminate\Http\Request;

class RentalContractEquipmentController extends Controller
{
    public function index(Request $request)
    {
        $items = RentalContractEquipment::where('school_id', $request->user()->school_id)->get();
        return response()->json(['data' => $items]);
    }

    public function store(RentalContractEquipmentRequest $request)
    {
        $item = RentalContractEquipment::create($request->validated());
        return response()->json(['data' => $item], 201);
    }

    public function show(RentalContractEquipment $contractEquipment)
    {
        $this->authorize('view', $contractEquipment);
        return response()->json(['data' => $contractEquipment]);
    }

    public function update(RentalContractEquipmentRequest $request, RentalContractEquipment $contractEquipment)
    {
        $this->authorize('update', $contractEquipment);
        $contractEquipment->update($request->validated());
        return response()->json(['data' => $contractEquipment]);
    }

    public function destroy(RentalContractEquipment $contractEquipment)
    {
        $this->authorize('delete', $contractEquipment);
        $contractEquipment->delete();
        return response()->json(['message' => 'Item deleted']);
    }
}
