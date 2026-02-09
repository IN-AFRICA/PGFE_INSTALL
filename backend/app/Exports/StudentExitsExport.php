<?php

namespace App\Exports;

use App\Models\StudentExit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentExitsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return StudentExit::with(['student', 'classroom', 'academicLevel', 'filiaire', 'cycle'])
            ->get();
    }

    public function map($exit): array
    {
        return [
            $exit->id,
            optional($exit->student)->matricule,
            optional($exit->student)->first_name,
            optional($exit->student)->last_name,
            optional($exit->academicLevel)->name,
            optional($exit->classroom)->name,
            optional($exit->filiaire)->name,
            optional($exit->cycle)->name,
            $exit->reason,
            $exit->exit_date,
            $exit->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Matricule',
            'First Name',
            'Last Name',
            'Academic Level',
            'Classroom',
            'Filiaire',
            'Cycle',
            'Reason',
            'Exit Date',
            'Created At',
        ];
    }
}
