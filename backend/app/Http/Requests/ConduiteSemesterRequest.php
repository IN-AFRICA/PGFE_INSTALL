<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConduiteSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conduite_id' => 'required|integer|exists:conduites,id',
            'school_year_id' => 'required|integer|exists:school_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ];
    }
}
