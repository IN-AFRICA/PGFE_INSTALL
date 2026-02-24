<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\AcademicPersonal;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class SuperAdminDashboardController extends Controller
{
    /**
     * Get global statistics for the super admin portal
     */
    public function index(): JsonResponse
    {
        $stats = [
            'total_schools' => School::count(),
            'total_students' => Student::count(),
            'total_personals' => AcademicPersonal::count(),
            'total_revenue' => (float) Payment::sum('amount'),
        ];

        // Revenue by school (global sync timestamp used for now)
        $schools_breakdown = School::select('id', 'name', 'code')
            ->get()
            ->map(function($school) {
                $globalLastSync = DB::table('sync_logs')->max('last_sync_at');

                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'code' => $school->code,
                    'students_count' => Student::where('school_id', $school->id)->count(),
                    'revenue' => (float) Payment::whereHas('student', function($q) use ($school) {
                        $q->where('school_id', $school->id);
                    })->sum('amount'),
                    'last_sync' => $globalLastSync,
                ];
            });

        // Distribution by province
        $province_stats = \App\Models\Province::select('id', 'name')
            ->withCount(['schools', 'students'])
            ->get();

        // Monthly revenue (Last 6 months)
        $monthly_revenue = Payment::selectRaw('MONTH(paid_at) as month, YEAR(paid_at) as year, SUM(amount) as total')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'global_stats' => $stats,
                'schools_breakdown' => $schools_breakdown,
                'province_distribution' => $province_stats,
                'monthly_revenue' => $monthly_revenue,
                'recent_activity' => [
                    'students' => Student::latest()->take(5)->get(['id', 'name', 'firstname', 'lastname', 'created_at', 'school_id']),
                    'payments' => Payment::with(['student:id,name,firstname,school_id'])->latest()->take(5)->get(['id', 'amount', 'student_id', 'paid_at']),
                ]
            ]
        ]);
    }

    /**
     * Search for students or personnel across all schools
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $q = $request->query('q');
        if (!$q) return response()->json(['data' => []]);

        $students = Student::where('name', 'like', "%$q%")
            ->orWhere('firstname', 'like', "%$q%")
            ->orWhere('lastname', 'like', "%$q%")
            ->take(10)
            ->get(['id', 'name', 'firstname', 'lastname', 'school_id']);

        $personals = AcademicPersonal::where('name', 'like', "%$q%")
            ->orWhere('pre_name', 'like', "%$q%")
            ->take(10)
            ->get(['id', 'name', 'pre_name', 'school_id']);

        return response()->json([
            'status' => true,
            'data' => [
                'students' => $students,
                'personals' => $personals
            ]
        ]);
    }

    /**
     * Get sync monitoring status for all schools
     */
    public function syncMonitoring(): JsonResponse
    {
        $monitoring = School::select('id', 'name', 'code')
            ->get()
            ->map(function($school) {
                // sync_logs store a global last_sync_at per table.
                // Use the OLDEST (min) last_sync_at to detect that at least one
                // table is late in synchronization.
                $lastSync = DB::table('sync_logs')
                    ->min('last_sync_at');

                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'code' => $school->code,
                    'last_sync' => $lastSync ?: null,
                    'status' => $this->getSyncStatus($lastSync ?: null)
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $monitoring
        ]);
    }

    private function getSyncStatus(?string $lastSync): string
    {
        // Si aucune synchronisation n'a encore eu lieu, on considère l'état comme critique
        if (! $lastSync) {
            return 'danger';
        }

        $diff = now()->diffInHours(\Illuminate\Support\Carbon::parse($lastSync));

        if ($diff < 24) {
            return 'ok';
        }

        if ($diff < 72) {
            return 'warning';
        }

        return 'danger';
    }
}
