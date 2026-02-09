<?php

namespace App\Http\Controllers\Insertion;

use App\Models\Insertion\Candidate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CandidateController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);
        $candidate = Candidate::create($validated);
        return response()->json($candidate, 201);
    }
}
