<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Period;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

class PeriodWebController extends BaseController
{
    public function index()
    {
        $periods = Period::all();
        return View::make('backend.pages.periods.index', compact('periods'));
    }
}
