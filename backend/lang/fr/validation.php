<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :min.',
    ],
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'date' => 'Le champ :attribute doit être une date valide.',
    'nullable' => '',
    'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    // Ajoutez d'autres règles personnalisées ici si besoin
    'attributes' => [
        'name' => 'nom',
        'category_id' => 'catégorie',
        'provider_id' => 'fournisseur',
        'min_threshold' => 'seuil minimum',
        'max_threshold' => 'seuil maximum',
        'quantity' => 'quantité',
        'contact' => 'contact',
        'address' => 'adresse',
        'article_id' => 'article',
        'entry_date' => 'date d\'entrée',
        'exit_date' => 'date de sortie',
        'reason' => 'motif',
        'note' => 'note',
        'state_date' => 'date d\'état',
        'inventory_date' => 'date d\'inventaire',
    ],
];
