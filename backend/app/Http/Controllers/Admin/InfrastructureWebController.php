<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller as BaseController;

class InfrastructureWebController extends BaseController
{
    public function index()
    {
        // Affiche la liste des infrastructures
        return View::make('backend.pages.infrastructures.index');
    }
}
