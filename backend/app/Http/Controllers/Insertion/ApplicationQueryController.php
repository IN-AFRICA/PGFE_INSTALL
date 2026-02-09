<?php

namespace App\Http\Controllers\Insertion;

use App\Models\Insertion\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApplicationQueryController extends Controller
{
    public function candidateApplications($id)
    {
        $applications = Application::where('candidate_id', $id)->get();
        return response()->json($applications);
    }
}
