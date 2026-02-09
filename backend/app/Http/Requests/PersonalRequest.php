<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => ['required', 'string', 'max:100', 'unique:personals,matricule'],
            'name' => ['required', 'string', 'max:100'],
            'post_name' => ['required', 'string', 'max:100'],
            'pre_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'max:20'],
            'civil_status' => ['required', 'string', 'max:50'],
            'country_id' => ['required', 'exists:countries,id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'territory_id' => ['required', 'exists:territories,id'],
            'commune_id' => ['required', 'exists:communes,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'type_id' => ['required', 'exists:types,id'],
            'physical_address' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'birth_place' => ['required', 'string', 'max:100'],
            'identity_card_number' => ['required', 'string', 'max:50'],
            'father_id' => ['nullable', 'exists:personals,id'],
            'mother_id' => ['nullable', 'exists:personals,id'],
            'academic_level_id' => ['nullable', 'exists:academic_levels,id'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'mechanisation_id' => ['required', 'exists:mecanisations,id'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricule.required' => 'Le matricule est requis',
            'matricule.unique' => 'Ce matricule existe déjà',
            'name.required' => 'Le nom est requis',
            'post_name.required' => 'Le post-nom est requis',
            'pre_name.required' => 'Le pré-nom est requis',
            'gender.required' => 'Le genre est requis',
            'civil_status.required' => "L'état civil est requis",
            'country_id.required' => 'Le pays est requis',
            'province_id.required' => 'La province est requise',
            'territory_id.required' => 'Le territoire est requis',
            'commune_id.required' => 'La commune est requise',
            'school_id.exists' => "L'école sélectionnée n'existe pas",
            'type_id.required' => 'Le type est requis',
            'physical_address.required' => "L'adresse physique est requise",
            'birth_date.required' => 'La date de naissance est requise',
            'birth_place.required' => 'Le lieu de naissance est requis',
            'identity_card_number.required' => "Le numéro de carte d'identité est requis",
            'father_id.exists' => "Le père sélectionné n'existe pas",
            'mother_id.exists' => "La mère sélectionnée n'existe pas",
            'academic_level_id.exists' => "Le niveau scolaire sélectionné n'existe pas",
            'phone.required' => 'Le téléphone est requis',
            'email.email' => "L'email doit être valide",
            'fonction_id.required' => 'La fonction est requise',
            'mechanisation_id.required' => 'La mécanisation est requise',
        ];
    }
}
