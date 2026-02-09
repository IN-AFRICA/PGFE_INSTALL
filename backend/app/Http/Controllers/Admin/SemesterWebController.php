<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Semester;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

class SemesterWebController extends BaseController
{
    public function index()
    {
        $semesters = Semester::all();
        return View::make('backend.pages.semesters.index', compact('semesters'));
    }
}
