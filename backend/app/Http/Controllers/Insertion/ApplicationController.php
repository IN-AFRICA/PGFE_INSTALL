<?php

namespace App\Http\Controllers\Insertion;

use App\Models\Insertion\Application;
use App\Models\Insertion\JobOffer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApplicationController extends Controller
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|integer|exists:candidates,id',
            'job_offer_id' => 'required|integer|exists:job_offers,id',
        ]);
        $offer = JobOffer::findOrFail($validated['job_offer_id']);
        if (!$offer->is_open) {
            return response()->json(['error' => 'Job offer is closed'], 422);
        }
        $application = Application::create($validated + ['status' => 'pending']);
        return response()->json($application, 201);
    }

    public function accept($id)
    {
        $application = Application::findOrFail($id);
        $application->status = 'accepted';
        $application->save();
        return response()->json($application);
    }

    public function reject($id)
    {
        $application = Application::findOrFail($id);
        $application->status = 'rejected';
        $application->save();
        return response()->json($application);
    }
}
