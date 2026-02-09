<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SchoolYear;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class SchoolYearWebController extends BaseController
{
    public function index()
    {
        $schoolYears = SchoolYear::all();
        return View::make('backend.pages.school-years.index', compact('schoolYears'));
    }

    public function create()
    {
        return View::make('backend.pages.school-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        SchoolYear::create([
            'name' => $request->name,
            'is_active' => false,
        ]);
        return Redirect::route('admin.school-years.index');
    }

    public function activate($id)
    {
        $year = SchoolYear::findOrFail($id);
        // Désactive toutes les autres années
        SchoolYear::where('id', '!=', $id)->update(['is_active' => false]);
        $year->is_active = true;
        $year->save();
        return Redirect::route('admin.school-years.index');
    }
}
