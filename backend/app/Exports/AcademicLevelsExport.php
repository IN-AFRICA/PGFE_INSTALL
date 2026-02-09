<?php

namespace App\Exports;

use App\Models\AcademicLevel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AcademicLevelsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AcademicLevel::with(['cycle'])
            ->get();
    }

    public function map($level): array
    {
        return [
            $level->id,
            $level->name,
            optional($level->cycle)->name,
            $level->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Academic Level Name',
            'Cycle',
            'Created At',
        ];
    }
}
