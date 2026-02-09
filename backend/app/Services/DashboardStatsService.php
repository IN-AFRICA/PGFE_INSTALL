<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

final class DashboardStatsService
{
    /**
     * Retourne les statistiques étudiants (totaux, répartition, mensuel) avec filtres optionnels.
     *
     * @param  array  $filters  ['id'=>int,'school_id'=>int,'classroom_id'=>int,'filiere_id'=>int,'gender'=>string,'school_year_id'=>int]
     */
    public function getStudentStats(array $filters = []): array
    {
        $base = Registration::query();

        // Filtre par étudiant spécifique (id student)
        if (! empty($filters['id']) && is_numeric($filters['id'])) {
            $base->where('student_id', (int) $filters['id']);
        }

        // Filtres optionnels (hors année scolaire)
        if (! empty($filters['school_id']) && is_numeric($filters['school_id'])) {
            $base->where('registrations.school_id', (int) $filters['school_id']);
        }
        if (! empty($filters['classroom_id']) && is_numeric($filters['classroom_id'])) {
            $base->where('classroom_id', (int) $filters['classroom_id']);
        }
        if (! empty($filters['filiere_id']) && is_numeric($filters['filiere_id'])) {
            $base->where('academic_level_id', (int) $filters['filiere_id']);
        }
        // Statuts actifs uniquement
        $base->where('registration_status', true);

        // Base sans filtre d'année (pour calculer les totaux par année)
        $baseWithoutYear = clone $base;

        // Appliquer le filtre année scolaire sur la base principale uniquement
        if (! empty($filters['school_year_id']) && is_numeric($filters['school_year_id'])) {
            $base->where('school_year_id', (int) $filters['school_year_id']);
        }

        // Totaux
        $total = $base->count();
        $girls = (clone $base)->whereHas('student', function ($q) {
            $q->where('gender', 'Féminin');
        })->count();
        $boys = (clone $base)->whereHas('student', function ($q) {
            $q->where('gender', 'Masculin');
        })->count();

        // Répartition filtrée
        $filtered = null;
        if (!empty($filters['gender'])) {
            $label = $filters['gender'] === 'male' ? 'Masculin' : ($filters['gender'] === 'female' ? 'Féminin' : $filters['gender']);
            $filtered = [
                'gender' => $label,
                'count' => (clone $base)->whereHas('student', function ($q) use ($label) {
                    $q->where('gender', $label);
                })->count(),
            ];
        }

        // Totaux d'inscriptions par année scolaire (sans le filtre d'année)
        $bySchoolYearQuery = (clone $baseWithoutYear)
            ->join('school_years', 'registrations.school_year_id', '=', 'school_years.id')
            ->select('school_years.name as school_year_name', DB::raw('COUNT(*) as total'))
            ->groupBy('school_years.name');
        if (! empty($filters['school_id']) && is_numeric($filters['school_id'])) {
            $bySchoolYearQuery->where('registrations.school_id', (int) $filters['school_id']);
        }
        $bySchoolYear = $bySchoolYearQuery
            ->pluck('total', 'school_year_name')
            ->toArray();

        return [
            'total' => $total,
            'girls' => $girls,
            'boys' => $boys,
            'filtered' => $filtered,
            'by_school_year' => $bySchoolYear,
        ];
    }
}
