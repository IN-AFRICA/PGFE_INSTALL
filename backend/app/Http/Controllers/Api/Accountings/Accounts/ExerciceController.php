<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciceRequest;
use App\Http\Resources\ExerciceResource;
use App\Models\Exercice;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ExerciceController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Exercice::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        return ExerciceResource::collection($query->latest()->paginate(10));
    }

    public function store(ExerciceRequest $request): ExerciceResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;
            $exercice = Exercice::create($data);

            return new ExerciceResource($exercice);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'Erreur lors de la creation ',
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Exercice $exercice): ExerciceResource
    {
        return ExerciceResource::make($exercice);
    }

    public function update(ExerciceRequest $request, Exercice $exercice): ExerciceResource|\Illuminate\Http\JsonResponse
    {
        try {
            $exercice->update($request->validated());

            return new ExerciceResource($exercice);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Exercice $exercice): \Illuminate\Http\JsonResponse
    {
        try {
            $exercice->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
