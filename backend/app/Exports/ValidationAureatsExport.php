<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\ValidationAureat;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class ValidationAureatsExport implements FromQuery, Responsable, WithHeadings, WithMapping
{
    use Exportable;

    public string $fileName;

    public function __construct(
        private readonly ?string $classFilter = null,
        private readonly int $minPercentage = 80,
        private readonly bool $includeNullPercentage = false,
    ) {
        $cls = $this->classFilter ? preg_replace('/[^A-Za-z0-9_-]/', '-', $this->classFilter) : 'all';
        $this->fileName = sprintf('laureats_%s_%dplus.xlsx', $cls, $this->minPercentage);
    }

    public function query()
    {
        $q = ValidationAureat::query();

        if (! $this->includeNullPercentage) {
            $q->whereNotNull('percentage')
                ->where('percentage', '>=', $this->minPercentage);
        } else {
            // Inclure tous les enregistrements (quel que soit le pourcentage, y compris null)
            if ($this->minPercentage > 0) {
                // Cas défensif si on veut quand même appliquer un min
                $q->where(function ($sub) {
                    $sub->whereNull('percentage')->orWhere('percentage', '>=', $this->minPercentage);
                });
            }
        }

        if (! empty($this->classFilter)) {
            $q->where('class', $this->classFilter);
        }

        return $q->orderBy('last_name')
            ->orderBy('first_name');
    }

    public function headings(): array
    {
        // Entêtes alignées à l'import (sans 'registration_number' ni 'present')
        return [
            'last_name',
            'middle_name',
            'first_name',
            'gender',
            'department',
            'class',
            'year',
            'cycle',
            'comment',
            'pourcentage',
        ];
    }

    public function map($row): array
    {
        // Ordre identique à l'import, sans 'registration_number' ni 'present'
        return [
            $row->last_name,
            $row->middle_name,
            $row->first_name,
            $row->gender,
            $row->department,
            $row->class,
            $row->year,
            $row->cycle,
            $row->comment,
            (int) ($row->percentage ?? 0),
        ];
    }
}
