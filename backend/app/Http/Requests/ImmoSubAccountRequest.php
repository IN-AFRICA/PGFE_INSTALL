<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImmoSubAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'code' => 'string|max:255|unique:immo_sub_accounts,code',
            'immo_sub_account_id' => 'integer',
            'school_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
