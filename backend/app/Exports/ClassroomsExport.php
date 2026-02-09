<?php

namespace App\Exports;

use App\Models\Classroom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClassroomsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Classroom::with(['academicLevel', 'titulaire'])
            ->get();
    }

    public function map($classroom): array
    {
        return [
            $classroom->id,
            $classroom->name,
            optional($classroom->academicLevel)->name,
            optional($classroom->titulaire)->full_name ?? '',
            $classroom->indicator,
            $classroom->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Classroom Name',
            'Academic Level',
            'Titulaire',
            'Indicator',
            'Created At',
        ];
    }
}
