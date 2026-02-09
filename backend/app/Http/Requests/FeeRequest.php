<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FeeRequest extends FormRequest
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
        $schoolId = auth()->user()->school_id ?? null; // école déjà connue côté serveur
        $feeId = optional($this->route('fee'))->id ?? null;

        return [
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'exchange_rate_id' => 'required|exists:exchange_rates,id',
            'effective_date' => 'nullable|date',
        ];
    }

    /**
     * Custom validation messages for FeeRequest.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant (amount) est requis.',
            'amount.numeric' => 'Le montant doit être numérique.',
            'amount.min' => 'Le montant doit être supérieur ou égal à 0.',

            'currency_id.required' => 'La devise (currency_id) est requise.',
            'currency_id.exists' => 'La devise sélectionnée est introuvable.',

            'fee_type_id.required' => 'Le type de frais (fee_type_id) est requis.',
            'fee_type_id.exists' => 'Le type de frais sélectionné est introuvable.',
            'exchange_rate_id.exists' => 'Le Taux d\'echange est requis',
            'effective_date.date' => "La date d'effet (effective_date) doit être une date valide.",
        ];
    }
}
