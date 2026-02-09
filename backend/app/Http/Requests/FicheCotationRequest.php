<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FicheCotationRequest extends FormRequest
{
    /**
     * Autoriser ou non la requête.
     */
    public function authorize(): bool
    {
        // Exemple : return auth()->check();
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        $rules = [
            'action' => 'required|in:export,import',
        ];

        // Si l'action est import, le fichier devient obligatoire
        if ($this->input('action') === 'import') {
            $rules['file'] = 'required|mimes:xlsx,csv|max:5120';
            // 5MB max
        }

        return $rules;
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Le champ action est obligatoire.',
            'action.in' => 'L\'action doit être soit export soit import.',

            'file.required' => 'Le fichier est obligatoire pour l\'importation.',
            'file.mimes' => 'Le fichier doit être au format XLSX ou CSV.',
            'file.max' => 'La taille du fichier ne peut pas dépasser 5 MB.',
        ];
    }
}
