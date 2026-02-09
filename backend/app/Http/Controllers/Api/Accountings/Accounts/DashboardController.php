<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Services\Accounts\AccountingDashboardService;

final class DashboardController extends Controller
{
    public function index()
    {
        $stats = (new AccountingDashboardService())->getAccountingStats();
        return \response()->json([
            'success' => true,
            'message' => 'Dashboard comptabilité opérationnel',
            'data' => $stats
        ]);
    }
}
