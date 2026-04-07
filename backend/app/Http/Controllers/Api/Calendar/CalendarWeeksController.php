<?php

namespace App\Http\Controllers\Api\Calendar;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class CalendarWeeksController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $validated = $request->validate([
            'school_year_id' => [
                'required',
                Rule::exists('school_years', 'id')->where(function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId)->orWhereNull('school_id');
                }),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $schoolYear = SchoolYear::query()
            ->where(function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->findOrFail($validated['school_year_id']);

        [$startYear, $endYear] = $this->parseSchoolYearName($schoolYear->name);

        $month = (int) $validated['month'];

        // Convention (par défaut): année scolaire YYYY-YYYY
        // - Mois 7..12 => première année (startYear)
        // - Mois 1..6  => seconde année (endYear)
        // Si parsing impossible, fallback sur l'année courante.
        $calendarYear = $startYear && $endYear
            ? ($month <= 6 ? $endYear : $startYear)
            : (int) now()->format('Y');

        $firstDay = CarbonImmutable::create($calendarYear, $month, 1)->startOfDay();
        $lastDay = $firstDay->endOfMonth()->startOfDay();

        // Align to Monday of the first week that intersects the month
        $weekStart = $firstDay->startOfWeek(CarbonImmutable::MONDAY);

        $weeks = [];
        $currentDate = now()->toDateString();
        $currentWeekValue = null;
        while ($weekStart->lessThanOrEqualTo($lastDay)) {
            $weekEnd = $weekStart->addDays(4); // Vendredi

            // Une semaine est pertinente si elle commence dans le mois OU se termine dans le mois
            $overlapsMonth = ($weekStart->month === $month) || ($weekEnd->month === $month);

            if ($overlapsMonth) {
                $fullWeekEnd = $weekStart->addDays(6); // Dimanche
                $isCurrent = ($currentDate >= $weekStart->toDateString() && $currentDate <= $fullWeekEnd->toDateString());
                $weekData = [
                    'value' => (string) $weekStart->isoWeek(),
                    'week_number' => $weekStart->isoWeek(),
                    'label' => sprintf(
                        'Semaine %d (%s - %s)',
                        $weekStart->isoWeek(),
                        $weekStart->format('d/m'),
                        $weekEnd->format('d/m')
                    ),
                    'start_date' => $weekStart->toDateString(),
                    'end_date' => $weekEnd->toDateString(),
                    'is_current' => $isCurrent,
                ];
                if ($isCurrent) {
                    $currentWeekValue = $weekData['value'];
                }
                $weeks[] = $weekData;
            }
            $weekStart = $weekStart->addWeek();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'school_year_id' => (int) $schoolYear->id,
                'school_year_name' => $schoolYear->name,
                'calendar_year' => $calendarYear,
                'month' => $month,
                'weeks' => $weeks,
                'current_week' => $currentWeekValue,
            ],
        ]);
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function parseSchoolYearName(?string $name): array
    {
        if (!$name) {
            return [null, null];
        }

        if (preg_match('/(\d{4})\D+(\d{4})/', $name, $matches) !== 1) {
            return [null, null];
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($startYear < 1900 || $endYear < 1900 || $endYear < $startYear) {
            return [null, null];
        }

        return [$startYear, $endYear];
    }
}
