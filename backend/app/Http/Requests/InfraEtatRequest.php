<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraEtatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'infra_infrastructure_id' => 'integer',
            'infra_iventaire_infrastructure_id' => 'integer',
            'description' => 'string',
            'school_id' => 'integer',
            'academic_personal_id' => 'integer',
        ];
    }
}
