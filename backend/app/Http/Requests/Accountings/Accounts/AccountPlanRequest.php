<?php

declare(strict_types=1);

namespace App\Http\Requests\Accountings\Accounts;

use Illuminate\Foundation\Http\FormRequest;

final class AccountPlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'code' => 'required',
            'class_comptability_id' => ['required', 'exists:class_comptability,id'],
            'category_comptability_id' => ['required', 'exists:category_comptability,id'],

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'code.required' => 'Le code est obligatoire',
            'class_comptability_id.exists' => 'La Classe comptable doit exister',
            'category_comptability_id.exists' => 'La Categorie comptable doit exister',
        ];
    }
}
