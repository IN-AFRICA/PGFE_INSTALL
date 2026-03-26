<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AcademicLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cycleId = $this->input('cycle_id');
        /** @var \App\Models\User|null $user */
        $user = $this->user();
        $schoolId = $user?->school_id;

        return [
            'cycle_id' => ['required', 'exists:cycles,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_levels', 'name')
                    ->where(function ($query) use ($cycleId, $schoolId) {
                        $query->where('cycle_id', $cycleId);
                        if ($schoolId) {
                            $query->where('school_id', $schoolId);
                        }
                        // Support soft-deletes when deleted_at exists in table
                        $query->whereNull('deleted_at');
                    })
                    ->ignore($this->academic_level),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du niveau académique est requis',
            'name.unique' => 'Ce niveau académique existe déjà',
            'cycle_id.required' => 'Le cycle est requis',
            'cycle_id.exists' => 'Le cycle sélectionné n\'existe pas',
        ];
    }
}
