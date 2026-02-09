<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\FicheCotation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

final class FicheCotationExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly ?int $schoolYearId = null,
        private readonly ?int $classroomId = null,
        private readonly ?int $courseId = null,
        private readonly ?int $filiaireId = null,
    ) {
    }

    public function collection()
    {
        $query = FicheCotation::with(['schoolYear', 'student', 'classroom', 'semester', 'course']);

        if ($this->schoolYearId !== null) {
            $query->where('school_year_id', $this->schoolYearId);
        }

        if ($this->classroomId !== null) {
            $query->where('classroom_id', $this->classroomId);
        }

        if ($this->courseId !== null) {
            $query->where('course_id', $this->courseId);
        }

        if ($this->filiaireId !== null) {
            $query->whereHas('classroom.academicLevel.cycle', function ($q) {
                $q->where('filiaire_id', $this->filiaireId);
            });
        }

        return $query->get()->map(function ($fiche) {
            return [
                'ID' => $fiche->id,
                'Année scolaire' => $fiche->schoolYear->name ?? '',
                'Étudiant' => $fiche->student->name ?? '',
                'Classe' => $fiche->classroom->name ?? '',
                'Semestre' => $fiche->semester->name ?? '',
                'Cours' => $fiche->course->name ?? '',
                'Note' => $fiche->note,
                'Maxima' => $fiche->Maxima,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Année Scolaire',
            'Étudiant',
            'Classe',
            'Semestre',
            'Cours',
            'Note',
            'Maxima',
        ];
    }
}
