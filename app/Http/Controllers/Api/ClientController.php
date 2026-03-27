<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clients = Client::query()
            ->where('user_id', auth('api')->id())
            ->withCount('tasks')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->search}%")
                  ->orWhere('company', 'ilike', "%{$request->search}%")
                  ->orWhere('email', 'ilike', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create([
            ...$request->validated(),
            'user_id' => auth('api')->id(),
        ]);

        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        if ($client->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $client->load(['tasks' => fn($q) => $q->orderByRaw(
            "CASE status WHEN 'in_progress' THEN 1 WHEN 'todo' THEN 2 WHEN 'review' THEN 3 ELSE 4 END"
        )]);
        $client->loadCount('tasks');

        return response()->json($client);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        if ($client->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $client->update($request->validated());
        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        if ($client->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $client->delete();
        return response()->json(null, 204);
    }
}