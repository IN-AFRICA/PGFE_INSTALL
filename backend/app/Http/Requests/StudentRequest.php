<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CivilStatusEnum;
use App\Enums\GenderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->student?->id ?? null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $required = $isUpdate ? ['sometimes', 'required'] : ['required'];
        $nullable = $isUpdate ? ['sometimes', 'nullable'] : ['nullable'];
        $studentId = $this->student?->id ?? 'NULL';

        return [
            'matricule' => array_merge(
                ['string', 'max:100', 'unique:students,matricule,'.$studentId],
                $isUpdate ? ['sometimes'] : ['nullable']
            ),
            'name' => [...$required, 'string', 'max:100'],
            'firstname' => [...$required, 'string', 'max:100'],
            'lastname' => [...$required, 'string', 'max:100'],
            'gender' => [...$required, new Enum(GenderEnum::class)],
            'civil_status' => [...$required, new Enum(CivilStatusEnum::class)],
            'address' => [...$required, 'string', 'max:255'],
            'birth_date' => [...$required, 'date'],
            'birth_place' => [...$required, 'string', 'max:100'],
            'phone_number' => [...$nullable, 'string', 'max:20', 'unique:students,phone_number,'.$studentId],
            'email' => [...$nullable, 'email', 'max:255', 'unique:students,email,'.$studentId],
            'image' => [...$nullable], // upload image optionnel
            'country_id' => [...$required, 'exists:countries,id'],
            'province_id' => [...$required, 'exists:provinces,id'],
            'territory_id' => [...$required, 'exists:territories,id'],
            'commune_id' => [...$required, 'exists:communes,id'],
            'parents_id' => [...$nullable, 'exists:parents,id'],
            'parent2_id' => [...$nullable, 'exists:parents,id'],
            'parent3_id' => [...$nullable, 'exists:parents,id'],
            'classroom_id' => [...$nullable, 'exists:classrooms,id'],
            'academic_level_id' => [...$nullable, 'exists:academic_levels,id'],
            'academic_personal_id' => [...$nullable, 'exists:academic_personals,id'],
            'filiaire_id' => [...$nullable, 'exists:filiaires,id'],
            'cycle_id' => [...$nullable, 'exists:cycles,id'],
            'note' => [...$nullable, 'string'],
            'percentageObtained' => [...$nullable, 'numeric', 'min:0', 'max:100'],
            'previousSchool' => [...$nullable, 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $levelId = (int) $this->input('academic_level_id');
            $cycleId = (int) $this->input('cycle_id');
            $filiaireId = (int) $this->input('filiaire_id');
            $classroomId = (int) $this->input('classroom_id');

            // 1) Vérifier que le niveau appartient au cycle (si les deux fournis)
            if ($levelId && $cycleId) {
                $level = \App\Models\AcademicLevel::query()->find($levelId);
                if ($level && (int) ($level->cycle_id ?? 0) !== $cycleId) {
                    $validator->errors()->add('cycle_id', "Le niveau académique indiqué n'appartient pas au cycle sélectionné.");
                }
            }
            // 2) Vérifier que le cycle appartient à la filière (si les deux fournis)
            if ($cycleId && $filiaireId) {
                $cycle = \App\Models\Cycle::query()->find($cycleId);
                if ($cycle && (int) ($cycle->filiaire_id ?? 0) !== $filiaireId) {
                    $validator->errors()->add('filiaire_id', "Le cycle sélectionné n'est pas rattaché à la filière indiquée.");
                }
            }
            // 3) Vérifier que la classe appartient au niveau académique (si les deux fournis)
            if ($classroomId && $levelId) {
                $classroom = \App\Models\Classroom::query()->find($classroomId);
                if ($classroom && (int) ($classroom->academic_level_id ?? 0) !== $levelId) {
                    $validator->errors()->add('classroom_id', "La classe sélectionnée n'est pas rattachée au niveau académique indiqué.");
                }
            }
        });
    }
}
