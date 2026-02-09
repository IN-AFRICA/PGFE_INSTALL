<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Contexte élève obligatoire
            'student_id' => 'required|exists:students,id',

            // Références existantes conservées
            'fee_id' => 'required|exists:fees,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
            'account_id' => 'required|exists:accounts,id',

            // Montants
            'amount' => 'required|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',

            // Métadonnées
            'details' => 'nullable|string',
            'paid_at' => 'nullable|date',
            'confirmed_at' => 'nullable|date',
            'refunded_at' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'student_id.required' => 'L\'identifiant de l\'élève est obligatoire.',
            'student_id.exists' => 'L\'élève spécifié n\'existe pas.',
            'fee_id.required' => 'L\'identifiant des frais est obligatoire.',
            'fee_id.exists' => 'Les frais spécifiés n\'existent pas.',
            'payment_method_id.required' => 'L\'identifiant du mode de paiement est obligatoire.',
            'payment_method_id.exists' => 'Le mode de paiement spécifié n\'existe pas.',
            'currency_id.required' => 'L\'identifiant de la devise est obligatoire.',
            'currency_id.exists' => 'La devise spécifiée n\'existe pas.',
            'account_id.required' => 'L\'identifiant du compte est obligatoire.',
            'account_id.exists' => 'Le compte spécifié n\'existe pas.',
            'amount.required' => 'Le montant est obligatoire.',
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
