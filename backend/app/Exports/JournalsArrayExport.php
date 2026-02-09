<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

final class JournalsArrayExport implements FromArray, WithHeadings
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Montant',
            'Compte',
            'Input',
            'Output',
            'Plan',
            'Sous-plan',
        ];
    }
}
