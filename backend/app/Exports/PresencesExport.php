<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Presence;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class PresencesExport implements FromQuery, Responsable, WithHeadings, WithMapping
{
    use Exportable;

    public string $fileName;

    public function __construct(
        private readonly ?string $dateDebut = null,
        private readonly ?string $dateFin = null,
        private readonly ?int $schoolId = null,
        private readonly ?int $classroomId = null,
    ) {
        $d1 = $this->dateDebut ?: now()->format('Y-m-d');
        $d2 = $this->dateFin ?: $d1;
        $this->fileName = 'presences_'.$d1.'_to_'.$d2.'.xlsx';
    }

    public function query()
    {
        $q = Presence::query()
            ->with(['student', 'school', 'classroom']);

        if ($this->schoolId) {
            $q->where('presences.school_id', $this->schoolId);
        }
        if ($this->classroomId) {
            $q->where('presences.classroom_id', $this->classroomId);
        }
        if ($this->dateDebut && $this->dateFin) {
            $q->whereBetween('presences.created_at', [$this->dateDebut, $this->dateFin]);
        } elseif ($this->dateDebut) {
            $q->whereDate('presences.created_at', $this->dateDebut);
        }

        $q->leftJoin('students', 'students.id', '=', 'presences.student_id')
            ->select('presences.*')

            ->orderBy('presences.created_at', 'DESC')

            // tri secondaire (optionnel)
            ->orderBy('students.lastname')
            ->orderBy('students.firstname')
            ->orderBy('students.name');

        return $q;
    }

    public function headings(): array
    {
        return [
            'Date',
            'École',
            'Classe',
            'Élève',
            'Présence',
        ];
    }

    public function map($row): array
    {
        $studentName = null;
        if ($row->student) {
            $parts = array_filter([
                $row->student->lastname ?? null,
                $row->student->firstname ?? null,
                $row->student->name ?? null,
            ]);
            $studentName = $parts ? implode(' ', $parts) : ($row->student->id ?? '');
        }

        // Priorité corrigée
        $status = 'Présent';
        if (! empty($row->absent_justified)) {
            $status = 'Absence justifiée';
        } elseif ($row->presence === false || (is_numeric($row->presence) && (int) $row->presence === 0)) {
            $status = 'Absence non justifiée';
        } elseif (! empty($row->sick)) {
            $status = 'Malade';
        }

        return [
            optional($row->created_at)?->toDateString(),
            $row->school->name ?? '',
            $row->classroom->name ?? '',
            $studentName,
            $status,
        ];
    }
}
