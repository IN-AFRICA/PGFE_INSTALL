<?php

namespace App\Exports;

use App\Models\SubAccountPlan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubAccountPlanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SubAccountPlan::with('accountPlan')
            ->select('id', 'name', 'code', 'account_plan_id', 'created_at', 'updated_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Account Plan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->code,
            optional($row->accountPlan)->name ?? '',
        ];
    }
}
