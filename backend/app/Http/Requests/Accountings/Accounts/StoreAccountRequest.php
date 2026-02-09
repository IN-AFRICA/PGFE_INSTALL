<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\Accounts;

use App\Models\Account;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;

final class StoreAccountRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new Unique(Account::class, 'name')],
            'code' => ['required', 'string', 'max:100', new Unique(Account::class, 'code')],
            'number' => ['required', 'string', 'max:100', new Unique(Account::class, 'number')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du compte est requis.',
            'name.string' => 'Le nom du compte doit être une chaîne de caractères.',
            'name.max' => 'Le nom du compte ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Le nom du compte doit être unique.',

            'code.required' => 'Le code du compte est requis.',
            'code.string' => 'Le code du compte doit être une chaîne de caractères.',
            'code.max' => 'Le code du compte ne doit pas dépasser 100 caractères.',
            'code.unique' => 'Le code du compte doit être unique.',

            'number.required' => 'Le numéro du compte est requis.',
            'number.string' => 'Le numéro du compte doit être une chaîne de caractères.',
            'number.max' => 'Le numéro du compte ne doit pas dépasser 100 caractères.',
            'number.unique' => 'Le numéro du compte doit être unique.',
        ];
    }
}
