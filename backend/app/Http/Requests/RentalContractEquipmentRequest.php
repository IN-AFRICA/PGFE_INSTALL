<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalContractEquipmentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'contract_equipment_code' => 'nullable|string|max:255',
            'rental_contract_id' => 'required|exists:rental_contracts,id',
            'equipment_id' => 'required|exists:equipments,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_hand_over' => 'nullable|boolean',
        ];
    }
}
