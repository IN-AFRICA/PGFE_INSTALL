<?php

namespace App\Http\Controllers\Insertion;

use App\Models\Insertion\JobOffer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobOfferController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'company_id' => 'required|integer|exists:companies,id',
        ]);
        $offer = JobOffer::create($validated + ['is_open' => true]);
        return response()->json($offer, 201);
    }

    public function close($id)
    {
        $offer = JobOffer::findOrFail($id);
        $offer->is_open = false;
        $offer->save();
        return response()->json($offer);
    }
}
