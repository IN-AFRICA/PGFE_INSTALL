<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InfraIventaireInfrastructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'infra_infrastructure_id' => 'integer',
            'description' => 'string',
            'school_id' => 'integer',
            'academic_personal_id' => 'integer',
        ];
    }
}
