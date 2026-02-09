<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mechanisations;

use App\Http\Controllers\Controller;
use App\Http\Requests\MechanisationRequest;
use App\Models\Mechanisation;

final class MechanisationController extends Controller
{
    public function index()
    {
        return Mechanisation::paginate(20);
    }

    public function store(MechanisationRequest $request)
    {
        $mechanisation = Mechanisation::create($request->validated());

        return response()->json($mechanisation, 201);
    }

    public function show($id)
    {
        $mechanisation = Mechanisation::findOrFail($id);

        return response()->json($mechanisation);
    }

    public function update(MechanisationRequest $request, $id)
    {
        $mechanisation = Mechanisation::findOrFail($id);
        $mechanisation->update($request->validated());

        return response()->json($mechanisation);
    }

    public function destroy($id)
    {
        $mechanisation = Mechanisation::findOrFail($id);
        $mechanisation->delete();

        return response()->json(null, 204);
    }
}
