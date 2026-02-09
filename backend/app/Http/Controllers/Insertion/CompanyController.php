<?php

namespace App\Http\Controllers\Insertion;

use App\Models\Insertion\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'contact' => 'required|string',
        ]);
        $company = Company::create($validated + [
            'school_id' => $user->school_id ?? null,
            'user_id' => $user->id ?? null,
        ]);
        return response()->json($company, 201);
    }
}
