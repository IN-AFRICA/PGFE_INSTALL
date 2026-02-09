<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|exists:students,id',
            'fee_id' => 'sometimes|exists:fees,id',
            'payment_method_id' => 'sometimes|exists:payment_methods,id',
            'currency_id' => 'sometimes|exists:currencies,id',

            'amount' => 'sometimes|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'paid_at' => 'nullable|date',
            'confirmed_at' => 'nullable|date',
            'refunded_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => "L'élève sélectionné n'existe pas.",
            'fee_id.exists' => 'Les frais sélectionnés n\'existent pas.',
            'payment_method_id.exists' => 'Le mode de paiement sélectionné n\'existe pas.',
            'currency_id.exists' => 'La devise sélectionnée n\'existe pas.',

            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être au moins :min.',

            'remaining_amount.numeric' => 'Le montant restant doit être un nombre.',
            'remaining_amount.min' => 'Le montant restant doit être au moins :min.',

            'details.string' => 'Les détails doivent être une chaîne de caractères.',
            'paid_at.date' => 'La date de paiement doit être une date valide.',
            'confirmed_at.date' => 'La date de confirmation doit être une date valide.',
            'refunded_at.date' => 'La date de remboursement doit être une date valide.',
        ];
    }
}
