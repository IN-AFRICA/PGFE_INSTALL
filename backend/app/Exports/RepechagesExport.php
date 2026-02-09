<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Repechage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class RepechagesExport implements FromCollection, WithHeadings, WithMapping
{
    private $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function map($repechage): array
    {
        return [
            $repechage->id,
            $repechage->student_id,
            $repechage->full_name,
            optional($repechage->classroom)->name,
            optional($repechage->filiaire)->name,
            optional($repechage->cycle)->name,
            optional($repechage->academicLevel)->name,
            $repechage->score_percent,
            $repechage->student_score,
            $repechage->is_eliminated ? 'Oui' : 'Non',
            $repechage->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'ID Élève',
            'Nom complet',
            'Classe',
            'Filière',
            'Cycle',
            'Niveau académique',
            'Score (%)',
            'Score élève',
            'Éliminé',
            'Date création',
        ];
    }
}
