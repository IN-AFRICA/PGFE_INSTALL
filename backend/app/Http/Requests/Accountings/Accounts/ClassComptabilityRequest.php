<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\Accounts;

use Illuminate\Foundation\Http\FormRequest;

final class ClassComptabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // tu peux restreindre selon les permissions plus tard
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:class_comptability,code,'.$this->id],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la classe comptable est obligatoire.',
            'code.required' => 'Le code de la classe comptable est obligatoire.',
            'code.unique' => 'Ce code existe déjà dans le système.',
        ];
    }
}
