<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\FicheCotation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class FicheCotationImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new FicheCotation([
            'school_year_id' => $row['school_year_id'],
            'student_id' => $row['student_id'],
            'classroom_id' => $row['classroom_id'],
            'semester_id' => $row['semester_id'],
            'course_id' => $row['course_id'],
            'note' => $row['note'],
        ]);
    }
}
