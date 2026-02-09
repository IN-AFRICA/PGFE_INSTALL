<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ClassroomRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms')->ignore($this->classroom),
            ],
            'indicator' => ['nullable', 'string', 'max:255'],
            'titulaire_id' => ['nullable', 'exists:academic_personals,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $academicLevelId = (int) $this->input('academic_level_id');
            if (! $academicLevelId) {
                return; // déjà couvert par d'autres règles
            }
            // Vérifier que le niveau académique existe bien et est lié à un cycle
            $academicLevel = \App\Models\AcademicLevel::with('cycle')->find($academicLevelId);
            if (! $academicLevel || ! $academicLevel->cycle) {
                $validator->errors()->add('academic_level_id', 'Le niveau académique sélectionné n\'est pas lié à un cycle valide.');
                return;
            }
        });
    }

    public function messages(): array
    {
        return [
            'academic_level_id.required' => 'Le niveau académique est requis',
            'academic_level_id.exists' => 'Le niveau académique sélectionné n\'existe pas',
            'name.required' => 'Le nom de la classe est requis',
            'name.unique' => 'Cette classe existe déjà pour ce niveau académique',
        ];
    }
}
