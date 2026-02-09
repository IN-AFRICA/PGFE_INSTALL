<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => $this->schoolIdRule('required|exists:schools,id'),
            'user_id' => 'required|exists:users,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_type_id' => 'required|exists:account_types,id',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate_id' => 'required|exists:exchange_rates,id',

            'beneficiary' => 'nullable|string',
            'expense_raison' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'amount_converted' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'nullable|date',
        ];
    }
}
