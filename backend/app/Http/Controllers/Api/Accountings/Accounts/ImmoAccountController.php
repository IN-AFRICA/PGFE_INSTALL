<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImmoAccountRequest;
use App\Http\Resources\ImmoAccountResource;
use App\Models\ImmoAccount;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImmoAccountController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = ImmoAccount::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        return ImmoAccountResource::collection($query->latest()->paginate(10));
    }

    public function store(ImmoAccountRequest $request): ImmoAccountResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;

            $immoAccount = ImmoAccount::create($data);

            return \response()->json([
                'data' => new ImmoAccountResource($immoAccount),
                'message' => 'Immo Account cree avec succees.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.',
                'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function show(ImmoAccount $immoAccount): ImmoAccountResource
    {
        return ImmoAccountResource::make($immoAccount);
    }

    public function update(ImmoAccountRequest $request, ImmoAccount $immoAccount): ImmoAccountResource|\Illuminate\Http\JsonResponse
    {
        try {
            $immoAccount->update($request->validated());

            return new ImmoAccountResource($immoAccount);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ImmoAccount $immoAccount): \Illuminate\Http\JsonResponse
    {
        try {
            $immoAccount->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
