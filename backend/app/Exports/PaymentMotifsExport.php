<?php

namespace App\Exports;

use App\Models\PaymentMotif;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentMotifsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return PaymentMotif::with('feeType')->get()->map(function ($motif) {
            return [
                'ID' => $motif->id,
                'Nom' => $motif->name,
                'Code' => $motif->code,
                'Type de frais' => optional($motif->feeType)->name,
                'Description' => $motif->description,
                'Créé le' => $motif->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Nom', 'Code', 'Type de frais', 'Description', 'Créé le'
        ];
    }
}
