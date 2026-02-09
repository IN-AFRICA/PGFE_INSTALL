<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StudentExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'student_id' => 'required|integer|exists:students,id',
            'exit_time' => 'required|date_format:H:i',
            'motif' => 'required|string',
            'filiere_id' => 'nullable|integer|exists:filiaires,id',
            'school_year_id' => 'required|integer|exists:school_years,id',
            'semester' => 'required|string',
        ];
    }
}
