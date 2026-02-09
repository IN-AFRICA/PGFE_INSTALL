<?php

namespace App\Exports;

use App\Models\Filiaire;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FiliairesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Filiaire::with(['cycles', 'school'])
            ->get();
    }

    public function map($filiaire): array
    {
        return [
            $filiaire->id,
            $filiaire->name,
            $filiaire->code,
            optional($filiaire->school)->name,
            $filiaire->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Filiaire Name',
            'Code',
            'School',
            'Created At',
        ];
    }
}
