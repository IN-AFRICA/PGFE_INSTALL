<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Http\Controllers\Api\Deliberation\GeneralDeliberationController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('validation-aureats:sync {--school_year_id=} {--classroom_id=} {--threshold=80} {--weight_by_hourly} {--skip_missing}', function () {
    $thresholdOption = $this->option('threshold');
    $threshold = $thresholdOption !== null ? (float) $thresholdOption : 80.0;
    $weightByHourly = (bool) $this->option('weight_by_hourly');
    $skipMissing = (bool) $this->option('skip_missing');

    $schoolYearId = $this->option('school_year_id');
    if (! $schoolYearId) {
        $schoolYearId = SchoolYear::where('is_active', true)->value('id');
    }

    $classroomId = $this->option('classroom_id');

    /** @var GeneralDeliberationController $controller */
    $controller = app(GeneralDeliberationController::class);

    $query = Registration::query()
        ->when($schoolYearId, static function ($q) use ($schoolYearId) {
            $q->where('school_year_id', $schoolYearId);
        })
        ->when($classroomId, static function ($q) use ($classroomId) {
            $q->where('classroom_id', $classroomId);
        })
        ->with(['student', 'classroom', 'filiaire', 'cycle', 'schoolYear']);

    $count = 0;
    $registrations = $query->get();

    if ($registrations->isEmpty()) {
        $this->info('Aucune inscription trouvée pour les critères donnés.');

        return 0;
    }

    foreach ($registrations as $registration) {
        $student = $registration->student;
        if (! $student) {
            continue;
        }

        $result = $controller->computeGeneralDeliberation(
            $student,
            (int) $registration->classroom_id,
            (int) $registration->school_year_id,
            $threshold,
            $weightByHourly,
            $skipMissing
        );

        if (($result['courses_empty'] ?? false) === true) {
            continue;
        }

        if (($result['overall_percentage'] ?? 0) >= 80) {
            $controller->markStudentAsLaureatFromDeliberation(
                $student,
                (int) $registration->classroom_id,
                (int) $registration->school_year_id,
                $result
            );
            $count++;
        }
    }

    $this->info("Synchronisation des lauréats terminée. Enregistrements traités : {$registrations->count()}, lauréats (>= 80%) mis à jour : {$count}.");
})->purpose('Recalcule automatiquement les lauréats à partir des délibérations pour tous les élèves.');
