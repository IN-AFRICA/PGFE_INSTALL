<?php

namespace App\Exports;

use App\Models\AccountPlan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountPlanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AccountPlan::with(['classComptability', 'categoryComptability'])
            ->select('id', 'name', 'code', 'class_comptability_id', 'category_comptability_id', 'user_id', 'created_at', 'updated_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Class Comptability',
            'Category Comptability',
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->code,
            optional($row->classComptability)->name ?? '',
            optional($row->categoryComptability)->name ?? '',
        ];
    }
}
