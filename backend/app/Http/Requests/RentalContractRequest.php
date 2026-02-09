<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalContractRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'contract_code' => 'nullable|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'start_date' => 'required|date',
            'loan_start_date' => 'nullable|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_amount' => 'nullable|numeric|min:0',
            'loan_term' => 'nullable|integer|min:1',
            'interest_rate' => 'nullable|numeric|min:0',
            'period_genre' => 'nullable|integer',
            'status' => 'required|string|in:active,inactive',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
