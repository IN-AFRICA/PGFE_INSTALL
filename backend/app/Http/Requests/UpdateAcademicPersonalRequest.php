<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CivilStatusEnum;
use App\Enums\GenderEnum;
use App\Models\AcademicPersonal;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\Territory;
use App\Models\Type;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Propaganistas\LaravelPhone\Rules\Phone;

final class UpdateAcademicPersonalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise les champs en entrée pour correspondre aux colonnes réelles.
     *
     * - phone_number -> phone
     * - identity_card -> identity_card_number
     * - address -> physical_address
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'post_name' => $this->input('post_name', $this->input('lastname', '')),
            'pre_name' => $this->input('pre_name', $this->input('firstname')),
            'phone' => $this->input('phone', $this->input('phone_number')),
            'identity_card_number' => $this->input('identity_card_number', $this->input('identity_card')),
            'physical_address' => $this->input('physical_address', $this->input('address')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Le modèle lié dans la route resource: /academic-personals/{academic_personal}
        $routeModel = $this->route('academic_personal');
        $currentId = is_object($routeModel) ? ($routeModel->id ?? null) : (is_numeric($routeModel) ? (int) $routeModel : null);

        return [
            'country_id' => ['required', new Exists(Country::class, 'id')],
            'province_id' => ['required', new Exists(Province::class, 'id')],
            'territory_id' => ['required', new Exists(Territory::class, 'id')],
            'commune_id' => ['required', new Exists(Commune::class, 'id')],
            'school_id' => ['required', new Exists(School::class, 'id')],
            'type_id' => ['required', new Exists(Type::class, 'id')],
            'father_id' => ['nullable', 'integer', 'exists:parents,id'],
            'mother_id' => ['nullable', 'integer', 'exists:parents,id'],
            'academic_level_id' => ['required', 'exists:academic_levels,id'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            // Ignorer le personnel courant pour les champs uniques
            'matricule' => ['required', 'string', 'max:255', (new Unique(AcademicPersonal::class, 'matricule'))->ignore($currentId)],
            'name' => ['required', 'string', 'max:255'],
            'post_name' => ['nullable', 'string', 'max:255'],
            'pre_name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                (new Unique(AcademicPersonal::class, 'phone'))->ignore($currentId),
                (new Phone())->country('CD'),
            ],
            'email' => ['required', 'email', (new Unique(AcademicPersonal::class, 'email'))->ignore($currentId)],
            'identity_card_number' => ['required', 'string', 'max:255', (new Unique(AcademicPersonal::class, 'identity_card_number'))->ignore($currentId)],
            'gender' => ['required', 'string', new Enum(GenderEnum::class)],
            'civil_status' => ['required', 'string', new Enum(CivilStatusEnum::class)],
            'birth_date' => ['required', 'date', 'date_format:Y-m-d'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'physical_address' => ['required', 'string', 'max:255'],
            // En update, image simplement nullable
            'image' => ['nullable'],
        ];
    }

    /**
     * Messages de validation personnalisés (français) pour la mise à jour.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country_id.required' => 'Le pays est obligatoire.',
            'country_id.exists' => 'Le pays sélectionné est invalide.',

            'province_id.required' => 'La province est obligatoire.',
            'province_id.exists' => 'La province sélectionnée est invalide.',

            'territory_id.required' => 'Le territoire est obligatoire.',
            'territory_id.exists' => 'Le territoire sélectionné est invalide.',

            'commune_id.required' => 'La commune est obligatoire.',
            'commune_id.exists' => 'La commune sélectionnée est invalide.',

            'school_id.required' => "L'école est obligatoire.",
            'school_id.exists' => "L'école sélectionnée est invalide.",

            'type_id.required' => 'Le type de personnel est obligatoire.',
            'type_id.exists' => 'Le type de personnel sélectionné est invalide.',

            'academic_level_id.required' => "Le niveau académique est obligatoire.",
            'academic_level_id.exists' => 'Le niveau académique sélectionné est invalide.',

            'fonction_id.required' => 'La fonction est obligatoire.',
            'fonction_id.exists' => 'La fonction sélectionnée est invalide.',

            'matricule.required' => 'Le matricule est obligatoire.',
            'matricule.unique' => 'Ce matricule est déjà utilisé pour un autre membre du personnel académique.',

            'name.required' => 'Le nom est obligatoire.',

            'pre_name.required' => 'Le prénom est obligatoire.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé pour un autre membre du personnel académique.',

            'email.required' => "L'adresse e-mail est obligatoire.",
            'email.email' => "L'adresse e-mail fournie n'est pas valide.",
            'email.unique' => "Cette adresse e-mail est déjà utilisée pour un autre membre du personnel académique.",

            'identity_card_number.required' => "Le numéro de la carte d'identité est obligatoire.",
            'identity_card_number.unique' => "Ce numéro de carte d'identité est déjà utilisé pour un autre membre du personnel académique.",

            'gender.required' => 'Le genre est obligatoire.',
            'gender.in' => 'Le genre sélectionné est invalide.',

            'civil_status.required' => 'L\'état civil est obligatoire.',
            'civil_status.in' => "L'état civil sélectionné est invalide.",

            'birth_date.required' => 'La date de naissance est obligatoire.',
            'birth_date.date' => 'La date de naissance doit être une date valide (format AAAA-MM-JJ).',

            'physical_address.required' => "L'adresse physique est obligatoire.",

            'image.string' => "L'image doit être une chaîne (chemin du fichier).",
            'image.max' => "Le chemin de l'image ne peut pas dépasser :max caractères.",
        ];
    }
}
