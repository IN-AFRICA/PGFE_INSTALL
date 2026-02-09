<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller as BaseController;

class MoisWebController extends BaseController
{
    public function index()
    {
        $mois = \App\Models\Mois::all();
        return View::make('backend.pages.mois.index', compact('mois'));
    }
}
