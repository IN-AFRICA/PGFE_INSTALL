<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\PersonPresence;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class PersonPresencesExport implements FromQuery, Responsable, WithHeadings, WithMapping
{
    use Exportable;

    public string $fileName;

    public function __construct(
        private readonly ?string $dateDebut = null,
        private readonly ?string $dateFin = null,
        private readonly ?int $schoolId = null,
    ) {
        $d1 = $this->dateDebut ?: now()->format('Y-m-d');
        $d2 = $this->dateFin ?: $d1;
        $this->fileName = 'person_presences_'.$d1.'_to_'.$d2.'.xlsx';
    }

    public function query()
    {
        $q = PersonPresence::query()
            ->with(['personnel', 'school'])
            ->leftJoin('academic_personals', 'academic_personals.id', '=', 'person_presences.personnel_id')
            ->select('person_presences.*');

        if ($this->schoolId) {
            $q->where('person_presences.school_id', $this->schoolId);
        }
        if ($this->dateDebut && $this->dateFin) {
            $q->whereBetween('person_presences.created_at', [$this->dateDebut, $this->dateFin]);
        } elseif ($this->dateDebut) {
            $q->whereDate('person_presences.created_at', $this->dateDebut);
        }

        return $q->orderBy('person_presences.created_at', 'DESC')
            ->orderBy('academic_personals.name');
    }

    public function headings(): array
    {
        return [
            'Date',
            'École',
            'Personnel',
            'Présence',
        ];
    }

    public function map($row): array
    {
        $parts = array_filter([
            $row->personnel->name ?? null,
            $row->personnel->post_name ?? null,
            $row->personnel->pre_name ?? null,
        ]);
        $personName = $parts ? implode(' ', $parts) : ($row->personnel->id ?? '');

        return [
            optional($row->created_at)?->toDateString(),
            $row->school->name ?? '',
            $personName,
            ($row->presence === true || (is_numeric($row->presence) && (int) $row->presence === 1)) ? 'Présent' : 'Absent',
        ];
    }
}
