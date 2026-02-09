<?php

namespace App\Exports;

use App\Models\Fee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FeesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Fee::with(['currency', 'feeType', 'school'])->get()->map(function ($fee) {
            return [
                'ID' => $fee->id,
                'Montant' => $fee->amount,
                'Devise' => optional($fee->currency)->name,
                'Type de frais' => optional($fee->feeType)->name,
                'Ecole' => optional($fee->school)->name,
                'Date effective' => $fee->effective_date,
                'Créé le' => $fee->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Montant', 'Devise', 'Type de frais', 'Ecole', 'Date effective', 'Créé le'
        ];
    }
}
