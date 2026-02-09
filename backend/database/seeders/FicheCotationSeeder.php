<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\FicheCotation;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Database\Seeder;

final class FicheCotationSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::first();
        if (! $semester) {
            $semester = Semester::create(['name' => '1 Semester']);
        }

        $schoolYears = SchoolYear::all();
        foreach ($schoolYears as $sy) {
            $students = Student::limit(5)->get();
            if ($students->isEmpty()) {
                continue;
            }
            foreach ($students as $student) {
                $classroom = Classroom::first();
                $course = Course::first();
                if (! $classroom || ! $course) {
                    continue;
                }
                FicheCotation::firstOrCreate([
                    'school_year_id' => $sy->id,
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'course_id' => $course->id,
                ], [
                    'note' => 12.0,
                ]);
            }
        }
    }
}
