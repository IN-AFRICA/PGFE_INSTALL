<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoursesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Course::with(['academicLevel', 'classroom', 'filiaire', 'cycle', 'academicPersonals'])
            ->get();
    }

    public function map($course): array
    {
        return [
            $course->id,
            $course->label,
            optional($course->academicLevel)->name ?? '',
            optional($course->classroom)->name ?? '',
            optional($course->filiaire)->name ?? '',
            optional($course->cycle)->name ?? '',
            $course->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Course Label',
            'Academic Level',
            'Classroom',
            'Filiaire',
            'Cycle',
            'Created At',
        ];
    }
}
