<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Repechage;

use App\Http\Controllers\Controller;
use App\Models\Repechage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

final class ImportRepechageController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        $file = $request->file('file');
        $imported = [];

        DB::beginTransaction();
        try {
            $rows = Excel::toArray([], $file)[0];
            // Skip header row
            foreach (array_slice($rows, 1) as $row) {
                // Ignore empty or incomplete rows
                if (empty($row[0]) || empty($row[4])) {
                    continue;
                }
                $data = [
                    'school_year' => $row[0],
                    'department' => $row[1] ?? null,
                    'class' => $row[2] ?? null,
                    'course' => $row[3] ?? null,
                    'full_name' => $row[4],
                    'score_percent' => isset($row[5]) ? (float) ($row[5]) : null,
                ];
                $imported[] = Repechage::create($data);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(Repechage::all());
    }
}
