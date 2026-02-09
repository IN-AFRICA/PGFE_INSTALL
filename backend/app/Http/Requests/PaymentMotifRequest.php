<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PaymentMotifRequest extends FormRequest
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
        $motifId = $this->route('payment_motif')?->id;

        return [
            'fee_type_id' => 'required|exists:fee_types,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_motifs,code,'.$motifId,
            'description' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages for PaymentMotif.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fee_type_id.required' => 'Le type de frais (fee_type_id) est requis.',
            'fee_type_id.exists' => 'Le type de frais sélectionné est introuvable.',

            'name.required' => 'Le nom du motif (name) est requis.',
            'name.string' => 'Le nom du motif doit être une chaîne de caractères.',
            'name.max' => 'Le nom du motif ne peut pas dépasser 255 caractères.',

            'code.required' => 'Le code du motif (code) est requis.',
            'code.string' => 'Le code du motif doit être une chaîne de caractères.',
            'code.max' => 'Le code du motif ne peut pas dépasser 50 caractères.',
            'code.unique' => 'Ce code de motif de paiement existe déjà.',

            'description.string' => 'La description doit être une chaîne de caractères.',
        ];
    }
}
