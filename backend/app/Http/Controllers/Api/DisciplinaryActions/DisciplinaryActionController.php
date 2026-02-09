<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\DisciplinaryActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisciplinaryActionRequest;
use App\Models\DisciplinaryAction;

final class DisciplinaryActionController extends Controller
{
    public function index()
    {
        return DisciplinaryAction::with(['student', 'school', 'type', 'author'])->paginate(20);
    }

    public function store(DisciplinaryActionRequest $request)
    {
        $action = DisciplinaryAction::create($request->validated());

        return response()->json($action, 201);
    }

    public function show($id)
    {
        $action = DisciplinaryAction::with(['student', 'school', 'type', 'author'])->findOrFail($id);

        return response()->json($action);
    }

    public function update(DisciplinaryActionRequest $request, $id)
    {
        $action = DisciplinaryAction::findOrFail($id);
        $action->update($request->validated());

        return response()->json($action);
    }

    public function destroy($id)
    {
        $action = DisciplinaryAction::findOrFail($id);
        $action->delete();

        return response()->json(null, 204);
    }
}
