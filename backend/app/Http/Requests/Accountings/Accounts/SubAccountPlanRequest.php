<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\Accounts;

use Illuminate\Foundation\Http\FormRequest;

final class SubAccountPlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'code' => 'required',
            'account_plan_id' => ['required', 'exists:account_plan,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'code.required' => 'Le code est obligatoire',
            'account_plan_id.required' => 'Le compte  est obligatoire',
        ];
    }
}
