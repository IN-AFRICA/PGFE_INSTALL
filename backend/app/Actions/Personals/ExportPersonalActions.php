<?php

declare(strict_types=1);

namespace App\Actions\Personals;

use App\Services\Exports\AcademicPersonalExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelAlias;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportPersonalActions
{
    public function execute(?Request $request = null): BinaryFileResponse
    {
        $filename = 'academic_personnel_'.now()->format('Y_m_d_H_i_s').'.xlsx';

        if ($request && $request->has('filters')) {
            return Excel::download(
                new AcademicPersonalExport($request->get('filters')),
                $filename,
                ExcelAlias::XLSX
            );
        }

        return Excel::download(new AcademicPersonalExport(), $filename, ExcelAlias::XLSX);
    }
}
