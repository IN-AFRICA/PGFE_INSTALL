<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CycleRequest extends FormRequest
{
    use SchoolIdRule;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\User|null $user */
        return [
            'filiaire_id' => [
                'required',
                'exists:filiaires,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cycles')->ignore($this->cycle),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'filiaire_id.required' => 'La filière est requise',
            'filiaire_id.exists' => 'La filière sélectionnée est invalide',
            'name.required' => 'Le nom du cycle est requis',
            'name.unique' => 'Ce cycle existe déjà',
        ];
    }
}
