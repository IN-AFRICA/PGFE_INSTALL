<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Bulletin;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Services\StudentBulletinService;

class BulletinPrintController extends Controller
{
    public function __construct(private readonly StudentBulletinService $service) {}

    /**
     * Imprimer tous les bulletins d'une classe sélectionnée
     */
    public function printByClass(Request $request, $classroomId)
    {
        $user = $request->user();
        $classroom = \App\Models\Classroom::findOrFail($classroomId);
        $students = \App\Models\Registration::where('classroom_id', $classroomId)
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter();
            
        $schoolYearId = $request->input('school_year_id');
        $files = [];
        $bulletinDir = storage_path('app/public/bulletins');
        if (!is_dir($bulletinDir)) {
            mkdir($bulletinDir, 0777, true);
        }

        foreach ($students as $student) {
            if (!$student) continue;
            $studentModel = $this->service->loadStudent($student->id, $schoolYearId ? (int)$schoolYearId : null);
            $resource = (new \App\Http\Resources\BulletinRessource\StudentBulletinResource($studentModel))->toArray($request);
            
            $html = view('bulletins.bulletin_dompdf', [
                'student' => $resource['student'] ?? [],
                'school' => $resource['registration']['school'] ?? [],
                'classe' => $resource['registration']['classroom'] ?? [],
                'grades' => $resource['grades'] ?? [],
                'summary' => $resource['summary'] ?? [],
                'schoolYear' => $resource['registration']['school_year']['name'] ?? '',
                'generatedAt' => $resource['generated_at'] ?? now()->format('Y-m-d H:i:s'),
            ])->render();

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $filename = 'bulletin_'.$classroom->id.'_'.($student->id).'.pdf';
            $fullPath = $bulletinDir . '/' . $filename;
            file_put_contents($fullPath, $dompdf->output());
            $files[] = $fullPath;
        }

        $zipPath = storage_path('app/public/bulletins/bulletins_classe_'.$classroom->id.'.zip');
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        foreach ($files as $file) {
            @unlink($file);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Imprimer le bulletin d'un élève spécifique
     */
    public function printByStudent(Request $request, $studentId)
    {
        $schoolYearId = $request->input('school_year_id');
        $studentModel = $this->service->loadStudent((int) $studentId, $schoolYearId ? (int) $schoolYearId : null);
        
        $resource = (new \App\Http\Resources\BulletinRessource\StudentBulletinResource($studentModel))->toArray($request);
        
        $data = [
            'student' => $resource['student'] ?? [],
            'school' => $resource['registration']['school'] ?? [],
            'classe' => $resource['registration']['classroom'] ?? [],
            'grades' => $resource['grades'] ?? [],
            'summary' => $resource['summary'] ?? [],
            'schoolYear' => $resource['registration']['school_year']['name'] ?? '',
            'generatedAt' => $resource['generated_at'] ?? now()->format('Y-m-d H:i:s'),
        ];

        $html = view('bulletins.bulletin_dompdf', $data)->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'bulletin_'.($data['student']['id'] ?? $studentId).'.pdf';

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
