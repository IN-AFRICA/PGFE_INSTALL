<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'province_id' => ['required', 'exists:provinces,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'city' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'unique:schools,name,'.$this->school?->id],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'phone_number' => [
                'nullable',
                'string',
                'regex:/^\+243[0-9]{9}$/',
                'unique:schools,phone_number,'.$this->school?->id,
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:schools,email,'.$this->school?->id,
            ],
            'type_id' => ['required', 'exists:types,id'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];
    }

    public function messages(): array
    {
        return [
            'province_id.required' => 'La province est requise',
            'province_id.exists' => 'La province sélectionnée n\'existe pas',
            'country_id.required' => 'Le pays est requis',
            'city.required' => 'La ville est requise',
            'name.required' => 'Le nom de l\'école est requis',
            'name.unique' => 'Cette école existe déjà',
            'address.required' => 'L\'adresse est requise',
            'phone_number.regex' => 'Le format du numéro doit être +243 suivi de 9 chiffres',
            'phone_number.unique' => 'Ce numéro de téléphone existe déjà',
            'email.email' => 'L\'email n\'est pas valide',
            'email.unique' => 'Cet email existe déjà',
            'type_id.required' => 'Le type est requis',
            'logo.image' => 'Le logo doit être une image',
            'logo.max' => 'Le logo ne doit pas dépasser 2MB',
        ];
    }
}
