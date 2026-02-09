<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardStatsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $service = new DashboardStatsService();
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer', 'min:1'],
            'gender' => ['nullable', 'string', 'max:15'],
            'months' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);
        $stats = $service->getStudentStats($validated);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
