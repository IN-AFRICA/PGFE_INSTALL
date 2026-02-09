<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'rental_contract_id' => 'required|exists:rental_contracts,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'paid_at' => 'required|date',
        ];
    }
}
