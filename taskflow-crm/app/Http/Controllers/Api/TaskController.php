<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where('user_id', auth('api')->id())
            ->with('client:id,name,company')
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->search,   fn($q) => $q->where('title', 'ilike', "%{$request->search}%"))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->paginate($request->per_page ?? 20);

        $clients = Client::query()->get();

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'user_id' => auth('api')->id(),
        ]);

        return response()->json($task->load('client:id,name,company'), 201);
    }

    public function show(Task $task): JsonResponse
    {
        if ($task->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($task->load('client'));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->update($request->validated());
        return response()->json($task->load('client:id,name,company'));
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task->update(['status' => $request->status]);
        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }
}