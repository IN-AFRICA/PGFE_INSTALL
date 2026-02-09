<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ValidationAureat;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\ValidationAureat;
use Illuminate\Http\Request;

final class ListValidationAureatController extends Controller
{
    public function index(Request $request)
    {
        $class = $request->input('class', $request->input('classe'));
        $classroomId = $request->input('classroom_id');
        $schoolYearId = $request->input('school_year_id');
        $yearName = $request->input('year');

        if ($classroomId && ! $class) {
            $classroom = Classroom::find($classroomId);
            if ($classroom) {
                $class = $classroom->name;
            }
        }

        if ($schoolYearId && ! $yearName) {
            $schoolYear = SchoolYear::find($schoolYearId);
            if ($schoolYear) {
                $yearName = $schoolYear->name;
            }
        }

        $q = ValidationAureat::query()
            ->where('percentage', '>=', 80);

        if (! empty($class)) {
            $q->where('class', $class);
        }

        if (! empty($yearName)) {
            $q->where('year', $yearName);
        }

        $items = $q->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json($items);
    }

    public function export(Request $request)
    {
        $class = $request->input('class', $request->input('classe'));
        $min = (int) $request->input('min', 80);

        return new \App\Exports\ValidationAureatsExport(
            classFilter: $class,
            minPercentage: $min,
        );
    }
}
