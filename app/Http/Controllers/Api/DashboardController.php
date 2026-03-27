<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth('api')->id();

        $taskStats = Task::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $priorityStats = Task::where('user_id', $userId)
            ->whereNotIn('status', ['done'])
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $clientStats = Client::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $overdueTasks = Task::where('user_id', $userId)
            ->overdue()
            ->with('client:id,name')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentTasks = Task::where('user_id', $userId)
            ->with('client:id,name')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        // Tasks concluídas por dia (últimos 7 dias)
        $completedByDay = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->where('updated_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(updated_at) as date, count(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalTasks     = Task::where('user_id', $userId)->count();
        $completedTasks = $taskStats['done'] ?? 0;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return response()->json([
            'stats' => [
                'tasks'           => $taskStats,
                'priority'        => $priorityStats,
                'clients'         => $clientStats,
                'total_tasks'     => $totalTasks,
                'completion_rate' => $completionRate,
                'active_clients'  => $clientStats['active'] ?? 0,
            ],
            'overdue_tasks'      => $overdueTasks,
            'recent_tasks'       => $recentTasks,
            'completed_by_day'   => $completedByDay,
        ]);
    }
}