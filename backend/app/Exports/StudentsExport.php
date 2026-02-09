<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Student::with(['classroom', 'academicLevel', 'filiaire', 'cycle'])
            ->get();
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->matricule,
            $student->first_name,
            $student->last_name,
            $student->gender,
            optional($student->academicLevel)->name,
            optional($student->classroom)->name,
            optional($student->filiaire)->name,
            optional($student->cycle)->name,
            $student->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Matricule',
            'First Name',
            'Last Name',
            'Gender',
            'Academic Level',
            'Classroom',
            'Filiaire',
            'Cycle',
            'Created At',
        ];
    }
}
