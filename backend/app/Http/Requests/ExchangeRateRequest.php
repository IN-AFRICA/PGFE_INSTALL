<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

final class ExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => 'required|exists:currencies,id',
            'rate' => 'required|numeric|min:0',
            'date_effective' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.required' => 'La devise (currency_id) est requise.',
            'currency_id.exists' => 'La devise sélectionnée est introuvable.',

            'rate.required' => 'Le taux de change (rate) est requis.',
            'rate.numeric' => 'Le taux de change doit être numérique.',
            'rate.min' => 'Le taux de change doit être supérieur ou égal à 0.',

            'date_effective.date' => "La date d'effet (date_effective) doit être une date valide.",
            'is_active.boolean' => 'Le champ is_active doit être vrai ou faux.',
        ];
    }

    /**
     * Retourne les données validées + school_id de l'utilisateur connecté.
     */
    public function validatedWithSchool(): array
    {
        $data = $this->validated();
        $data['school_id'] = data_get(Auth::user(), 'school_id');

        return $data;
    }
}
