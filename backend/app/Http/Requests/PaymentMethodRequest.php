<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Supporte store et update
        $routeModel = $this->route('paymentMethod') ?? $this->route('method') ?? null;
        $currentId = is_object($routeModel) ? ($routeModel->id ?? null) : (is_numeric($routeModel) ? (int) $routeModel : null);

        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

        return [
            'name' => $isUpdate
                ? ['sometimes', 'required', 'string', 'max:255']
                : ['required', 'string', 'max:255'],
            'code' => $isUpdate
                ? ['sometimes', 'required', 'string', 'max:255', Rule::unique('payment_methods', 'code')->ignore($currentId)]
                : ['required', 'string', 'max:255', Rule::unique('payment_methods', 'code')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la méthode de paiement (name) est requis.',
            'name.string' => 'Le nom de la méthode de paiement doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la méthode de paiement ne peut pas dépasser 255 caractères.',

            'code.required' => 'Le code de la méthode de paiement (code) est requis.',
            'code.string' => 'Le code de la méthode de paiement doit être une chaîne de caractères.',
            'code.max' => 'Le code de la méthode de paiement ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code de méthode de paiement existe déjà.',
        ];
    }
}
