<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateExpenseRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => $this->schoolIdRule('sometimes|exists:schools,id'),
            'user_id' => 'sometimes|exists:users,id',
            'payment_method_id' => 'sometimes|exists:payment_methods,id',
            'account_type_id' => 'sometimes|exists:account_types,id',
            'currency_id' => 'sometimes|exists:currencies,id',
            'exchange_rate_id' => 'sometimes|exists:exchange_rates,id',

            'beneficiary' => 'nullable|string',
            'expense_raison' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'amount_converted' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'nullable|date',
        ];
    }
}
