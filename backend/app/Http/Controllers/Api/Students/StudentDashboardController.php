<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Services\Students\StudentDashboardService;

final class StudentDashboardController extends Controller
{
    public function index()
    {
        $filters = [
            'school_year_id' => request('school_year_id'),
            'filiere_id' => request('filiere_id'), // section
            'classroom_id' => request('classroom_id'),
            'gender' => request('gender'),
        ];
        $stats = (new StudentDashboardService())->getStudentStats($filters);
        return \response()->json([
            'success' => true,
            'message' => 'Dashboard étudiant opérationnel',
            'data' => $stats
        ]);
    }
}
