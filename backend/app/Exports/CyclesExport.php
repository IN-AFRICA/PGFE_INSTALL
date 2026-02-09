<?php

namespace App\Exports;

use App\Models\Cycle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CyclesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Cycle::with(['filiaire'])
            ->get();
    }

    public function map($cycle): array
    {
        return [
            $cycle->id,
            $cycle->name,
            optional($cycle->filiaire)->name,
            $cycle->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cycle Name',
            'Filiaire',
            'Created At',
        ];
    }
}
