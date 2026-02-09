<?php

declare(strict_types=1);

namespace App\Services\Students;

use App\Services\DashboardStatsService;

final class StudentDashboardService
{
    /**
     * Retourne les statistiques du dashboard étudiant (nombre, inscriptions, etc.)
     * @param array $filters
     * @return array
     */
    public function getStudentStats(array $filters = []): array
    {
        $service = new DashboardStatsService();
        return $service->getStudentStats($filters);
    }
}
