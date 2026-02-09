<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraInventaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'infra_equipement_id' => 'integer',
            'observation' => 'string',
            'school_id' => 'integer',
            'author_id' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'infra_equipement_id.integer' => 'The infra_equipement_id must be an integer.',
            'observation.string' => 'The observation must be a string.',
            'school_id.integer' => 'The school_id must be an integer.',
            'author_id.integer' => 'The author_id must be an integer.',
        ];
    }
}
