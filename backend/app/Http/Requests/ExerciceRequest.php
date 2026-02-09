<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExerciceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'date.date' => 'La date doit être une date valide',
            'start_date.date' => 'La date de début doit être une date valide',
            'end_date.date' => 'La date de fin doit être une date valide',
            'end_date.after_or_equal' => 'La date de fin doit être le même jour ou après la date de début',
        ];
    }
}
