<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SchoolTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

final class TypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $values = implode(',', SchoolTypeEnum::values());

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'in:'.$values,
                'unique:types,title,'.($this->type?->id ?? 'null'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du type est requis',
            'title.unique' => 'Ce type existe déjà',
            'title.in' => 'Le type doit être soit "'.SchoolTypeEnum::FORMEL->value.'" soit "'.SchoolTypeEnum::NON_FORMEL->value.'"',
        ];
    }
}
