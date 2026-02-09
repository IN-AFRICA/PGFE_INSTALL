<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Journal;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class JournalsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public string $fileName;

    public function __construct(
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
        private readonly ?int $accountId = null,
        private readonly ?int $inputAccountId = null,
        private readonly ?int $outputAccountId = null,
        private readonly ?int $accountPlanId = null,
        private readonly ?int $subAccountPlanId = null,
        private readonly ?bool $includeAbandoned = null,
        private readonly ?bool $onlyAbandoned = null,
    ) {
        $d1 = $this->startDate ?: now()->toDateString();
        $d2 = $this->endDate ?: $d1;
        $this->fileName = 'journaux_'.$d1.'_to_'.$d2.'.xlsx';
    }

    public function query()
    {
        $q = Journal::query()
            ->with(['account', 'inputAccount', 'outputAccount', 'accountPlan', 'subAccountPlan'])
            ->orderBy('date');

        if ($this->startDate && $this->endDate) {
            $q->whereBetween('date', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate) {
            $q->whereDate('date', $this->startDate);
        }
        if ($this->accountId) {
            $q->where('account_id', $this->accountId);
        }
        if ($this->inputAccountId) {
            $q->where('input_account_id', $this->inputAccountId);
        }
        if ($this->outputAccountId) {
            $q->where('output_account_id', $this->outputAccountId);
        }
        if ($this->accountPlanId) {
            $q->where('account_plan_id', $this->accountPlanId);
        }
        if ($this->subAccountPlanId) {
            $q->where('sub_account_plan_id', $this->subAccountPlanId);
        }
        // État: par défaut, exclure les abandonnés (aligné avec contrôleur PDF)
        if ($this->onlyAbandoned) {
            $q->where('abandoned', true);
        } elseif (! $this->includeAbandoned) {
            $q->where('abandoned', false);
        }

        return $q->select('journals.*');
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

    public function map($row): array
    {
        return [
            $row->date,
            $row->description,
            (float) $row->montant,
            optional($row->account)->name ?? '',
            optional($row->inputAccount)->name ?? '',
            optional($row->outputAccount)->name ?? '',
            optional($row->accountPlan)->name ?? '',
            optional($row->subAccountPlan)->name ?? '',
        ];
    }
}
