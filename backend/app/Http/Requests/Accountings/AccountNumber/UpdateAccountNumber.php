<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\AccountNumber;

use App\Models\AccountNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;

final class UpdateAccountNumber extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                (new Unique(AccountNumber::class, 'name'))->ignore($this->route('accountNumber')),
            ],
            'class_account_id' => [
                'required',
                'exists:class_accounts,id',
            ],
        ];
    }
}
