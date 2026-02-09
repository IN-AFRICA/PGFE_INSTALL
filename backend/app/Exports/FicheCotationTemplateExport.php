<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

final class FicheCotationTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Aucune ligne de données, uniquement les en-têtes pour servir de modèle
        return collect([]);
    }

    public function headings(): array
    {
        // Doit correspondre à l'ordre attendu dans l'import
        // 0: school_year_id, 1: student_id, 2: classroom_id, 3: course_id, 4: note, 5: Maxima
        return [
            'school_year_id',
            'student_id',
            'classroom_id',
            'course_id',
            'note',
            'Maxima',
        ];
    }
}
