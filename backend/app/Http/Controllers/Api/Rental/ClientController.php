<?php
namespace App\Http\Controllers\Api\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::where('school_id', $request->user()->school_id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tenant_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100);

        $clients = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($clients);
    }

    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated());
        return response()->json(['data' => $client], 201);
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        return response()->json(['data' => $client]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);
        $client->update($request->validated());
        return response()->json(['data' => $client]);
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();
        return response()->json(['message' => 'Client deleted']);
    }
}
