<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Bulletin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BulletinRessource\StudentBulletinResource;
use App\Services\StudentBulletinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

final class BulletinController extends Controller
{
    public function __construct(private readonly StudentBulletinService $service) {}

    // JSON pour le front
    public function show(Request $request): StudentBulletinResource
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'school_year_id' => 'nullable|integer|exists:school_years,id',
        ]);

        $schoolYearId = array_key_exists('school_year_id', $data)
            ? (int) $data['school_year_id']
            : null;

        $student = $this->service->loadStudent(
            (int) $data['student_id'],
            $schoolYearId
        );

        return new StudentBulletinResource($student);
    }

    // Vue PDF imprimable
    public function print(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'school_year_id' => 'nullable|integer|exists:school_years,id',
        ]);

        $schoolYearId = array_key_exists('school_year_id', $data)
            ? (int) $data['school_year_id']
            : null;


        $studentModel = $this->service->loadStudent(
            (int) $data['student_id'],
            $schoolYearId
        );

        // Utiliser la resource pour obtenir toutes les données attendues par le blade
        $resource = (new \App\Http\Resources\BulletinRessource\StudentBulletinResource($studentModel))->toArray($request);

        // Extraction des variables attendues par le blade
        $student = $resource['student'] ?? [];
        $school = $resource['registration']['school'] ?? [];
        $classe = $resource['registration']['classroom'] ?? [];
        $grades = $resource['grades'] ?? [];
        $summary = $resource['summary'] ?? [];
        $schoolYear = $resource['registration']['school_year']['name'] ?? '';

        $html = view('bulletins.bulletin_dompdf', [
            'student' => $student,
            'school' => $school,
            'classe' => $classe,
            'grades' => $grades,
            'summary' => $summary,
            'schoolYear' => $schoolYear,
            'generatedAt' => $resource['generated_at'] ?? now()->format('Y-m-d H:i:s'),
        ])->render();

        $filename = 'bulletin_'.($student['id'] ?? 'eleve').'.pdf';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
