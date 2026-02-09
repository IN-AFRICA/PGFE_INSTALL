<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'mark_model' => 'nullable|string|max:255',
            'tech_specification' => 'nullable|string',
            'comments' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'daily_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
            'is_available' => 'nullable|boolean',
        ];
    }
}
