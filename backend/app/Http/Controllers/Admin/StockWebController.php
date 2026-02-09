<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller as BaseController;

class StockWebController extends BaseController
{
    public function index()
    {
        // Affiche la liste du stock
        return View::make('backend.pages.stock.index');
    }
}
