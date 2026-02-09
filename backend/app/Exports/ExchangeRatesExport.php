<?php

namespace App\Exports;

use App\Models\ExchangeRate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExchangeRatesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ExchangeRate::with('currency')->get()->map(function ($rate) {
            return [
                'ID' => $rate->id,
                'Devise' => optional($rate->currency)->name,
                'Taux' => $rate->rate,
                'Date effective' => $rate->date_effective,
                'Actif' => $rate->is_active ? 'Oui' : 'Non',
                'Créé le' => $rate->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Devise', 'Taux', 'Date effective', 'Actif', 'Créé le'
        ];
    }
}
