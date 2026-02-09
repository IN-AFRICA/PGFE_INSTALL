<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\AccountNumber;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreAccountNumber extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:account_numbers,name',
            ],
            'class_account_id' => [
                'required',
                'exists:class_accounts,id',
            ],
        ];
    }
}
