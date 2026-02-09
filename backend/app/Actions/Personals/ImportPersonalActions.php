<?php

declare(strict_types=1);

namespace App\Actions\Personals;

use App\Services\Imports\AcademicPersonalImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

final class ImportPersonalActions
{
    public function execute(Request $request): void
    {
        Excel::import(new AcademicPersonalImport, $request->file('file'));
    }
}
