<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Accountings\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImmoSubAccountRequest;
use App\Http\Resources\ImmoSubAccountResource;
use App\Models\ImmoSubAccount;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImmoSubAccountController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = ImmoSubAccount::query();

        $search = strtolower(trim((string) $request->input('search', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }

        return ImmoSubAccountResource::collection($query->latest()->paginate(10));
    }

    public function store(ImmoSubAccountRequest $request): ImmoSubAccountResource|\Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['school_id'] = $request->user()->school_id;
            $immoSubAccount = ImmoSubAccount::create($data);

            return response()->json([
                'data' => new ImmoSubAccountResource($immoSubAccount),
                'message' => 'Immo Sub Account cree avec succees.',
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'error' => 'There is an error.',
                'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(ImmoSubAccount $immoSubAccount): ImmoSubAccountResource
    {
        return ImmoSubAccountResource::make($immoSubAccount);
    }

    public function update(ImmoSubAccountRequest $request, ImmoSubAccount $immoSubAccount): ImmoSubAccountResource|\Illuminate\Http\JsonResponse
    {
        try {
            $immoSubAccount->update($request->validated());

            return new ImmoSubAccountResource($immoSubAccount);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ImmoSubAccount $immoSubAccount): \Illuminate\Http\JsonResponse
    {
        try {
            $immoSubAccount->delete();

            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (Exception $exception) {
            report($exception);

            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
