<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraInfrastructureInventaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'infra_infrastructure_id' => ['required', 'integer', 'exists:infra_infrastructures,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'inventory_date' => ['required', 'date'],
            'status' => ['required', 'in:excellent,bon,moyen,mauvais,critique'],
            'observations' => ['nullable', 'array'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'infra_infrastructure_id.required' => 'L\'infrastructure est obligatoire.',
            'infra_infrastructure_id.exists' => 'L\'infrastructure sélectionnée est invalide.',
            'title.required' => 'Le titre est obligatoire.',
            'inventory_date.required' => 'La date d\'inventaire est obligatoire.',
            'status.in' => 'Le statut doit être parmi: excellent, bon, moyen, mauvais, critique.',
        ];
    }
}
