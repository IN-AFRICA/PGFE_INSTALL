<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class AccountingDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('backend.pages.accounting.index');
    }
}
