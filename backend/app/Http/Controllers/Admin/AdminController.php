<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPersonal;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;

final class AdminController extends Controller
{
    /** Dashboard minimal purgé + filtre école pour les listes récentes */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $selected_school_id = $request->filled('school_id') ? (int) $request->integer('school_id') : session('selected_school_id');

        // Si le super admin sélectionne une école, on la stocke en session
        if ($user && $user->hasRole('super-admin')) {
            // Cas 1: paramètre présent et vide => effacer la sélection
            if ($request->has('school_id') && ! $request->filled('school_id')) {
                $request->session()->forget('selected_school_id');
                $request->session()->forget('selected_classroom_id');
                $selected_school_id = null;
            }
            // Cas 2: paramètre présent et non vide => définir nouvelle école
            elseif ($request->filled('school_id')) {
                // Changement d'école: réinitialiser la classe sélectionnée
                if (session('selected_school_id') !== (int) $request->integer('school_id')) {
                    $request->session()->forget('selected_classroom_id');
                }
                session(['selected_school_id' => $selected_school_id]);
            }
        }

        // Si aucune école n'est sélectionnée, afficher un message d'invitation
        $show_school_select = $user && $user->hasRole('super-admin') && ! $selected_school_id;

        // Compteurs: filtrés si école sélectionnée, sinon globaux
        $count_schools = $selected_school_id ? 1 : School::query()->count();

        // Classrooms n'ont plus de colonne school_id directe.
        // Pour le super admin avec une école sélectionnée, on filtre via la chaîne
        // academicLevel -> cycle -> filiaire -> school_id. Pour les autres rôles,
        // le ScopeBySchool sur Classroom applique déjà le filtrage par école.
        $count_classrooms = Classroom::query()
            ->when($selected_school_id, function ($q) use ($selected_school_id) {
                $q->whereHas('academicLevel.cycle.filiaire', function ($sub) use ($selected_school_id) {
                    $sub->where('school_id', $selected_school_id);
                });
            })
            ->count();
        $count_students = Student::query()
            ->when($selected_school_id, fn ($q) => $q->where('school_id', $selected_school_id))
            ->count();
        $count_personnels = AcademicPersonal::query()
            ->when($selected_school_id, fn ($q) => $q->where('school_id', $selected_school_id))
            ->count();

        // Listes récentes avec pagination légère sur le dashboard
        $recent_students = Student::query()
            ->when($selected_school_id, fn ($q) => $q->where('school_id', $selected_school_id))
            ->latest('id')
            ->paginate(8, ['id', 'name', 'firstname', 'lastname', 'gender', 'school_id'], 'students_page')
            ->withQueryString();

        $recent_personnels = AcademicPersonal::query()
            ->when($selected_school_id, fn ($q) => $q->where('school_id', $selected_school_id))
            ->latest('id')
            ->paginate(8, ['id', 'name', 'school_id'], 'personnels_page')
            ->withQueryString();

        $schools = School::query()->orderBy('name')->get(['id', 'name']);

        // Stats par province paginées si aucune école n'est sélectionnée
        $province_stats = null;
        if (! $selected_school_id) {
            $province_stats = \App\Models\Province::query()
                ->leftJoin('schools', 'provinces.id', '=', 'schools.province_id')
                ->leftJoin('students', 'provinces.id', '=', 'students.province_id')
                ->select('provinces.id', 'provinces.name')
                ->selectRaw('COUNT(DISTINCT schools.id) as schools')
                ->selectRaw('COUNT(DISTINCT students.id) as students')
                ->groupBy('provinces.id', 'provinces.name')
                ->orderByDesc('schools')
                ->paginate(10, ['provinces.id', 'provinces.name'], 'provinces_page')
                ->withQueryString();
        }

        // Determine if school-scoped actions are available
        $canManageSchoolScopedCheck = (bool) $selected_school_id;

        return view('backend.pages.dashboard.index', compact(
            'count_schools',
            'count_classrooms',
            'count_students',
            'count_personnels',
            'recent_students',
            'recent_personnels',
            'schools',
            'selected_school_id',
            'show_school_select',
            'province_stats',
            'canManageSchoolScopedCheck'
        ));
    }

    /**
     * Recherche globale multi-écoles (Web)
     */
    public function globalSearch(Request $request)
    {
        $q = $request->query('q');
        $students = [];
        $personals = [];

        if ($q) {
            $students = Student::where('name', 'like', "%$q%")
                ->orWhere('firstname', 'like', "%$q%")
                ->orWhere('lastname', 'like', "%$q%")
                ->with('school')
                ->latest()
                ->paginate(20, ['*'], 'students_page');

            $personals = AcademicPersonal::where('name', 'like', "%$q%")
                ->orWhere('pre_name', 'like', "%$q%")
                ->with('school')
                ->latest()
                ->paginate(20, ['*'], 'personals_page');
        }

        return view('backend.pages.admin.search', compact('students', 'personals', 'q'));
    }

    /**
     * Monitoring de synchronisation multi-écoles (Web)
     */
    public function syncMonitoring()
    {
        $schools = School::select('id', 'name', 'city')
            ->withCount(['students', 'personals'])
            ->get()
            ->map(function($school) {
                $lastSync = \Illuminate\Support\Facades\DB::table('sync_logs')
                    ->where('school_id', $school->id)
                    ->latest('last_sync')
                    ->first();

                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'city' => $school->city,
                    'students_count' => $school->students_count,
                    'personals_count' => $school->personals_count,
                    'last_sync' => $lastSync ? $lastSync->last_sync : null,
                    'status' => $this->calculateSyncStatus($lastSync ? $lastSync->last_sync : null)
                ];
            });

        return view('backend.pages.admin.sync_monitoring', compact('schools'));
    }

    private function calculateSyncStatus(?string $lastSync): string
    {
        if (!$lastSync) return 'never';
        $diff = now()->diffInHours(\Illuminate\Support\Carbon::parse($lastSync));
        if ($diff < 24) return 'ok';
        if ($diff < 72) return 'warning';
        return 'danger';
    }
}
