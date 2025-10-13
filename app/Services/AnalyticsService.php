<?php

namespace App\Services;

use App\Models\BackendGames;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function userAnalytics() {
        $users = User::all();

    }


    public function backendRequestAnalytics()
    {
        return BackendGames::with(['tasks.request'])
            ->get()
            ->map(function ($game) {
                $requests = $game->tasks->pluck('request')->filter();
                $types = ['freeplay', 'recharge', 'create', 'read', 'reset-password', 'withdraw'];

                $counts = collect($types)->mapWithKeys(fn($type) => [
                    "{$type}_count" => $requests->where('type', $type)->count()
                ]);

                return [
                    'game_name' => $game->name,
                    'total_requests' => $requests->count(),
                    ...$counts
                ];
            });
    }


    public function backendRequestStatusAnalytics()
    {
        return DB::table('automation_requests as req')
            ->leftJoin('automation_results as res', 'res.task_id', '=', 'req.task_id')
            ->select(
                'req.type',
                DB::raw("SUM(CASE WHEN res.status = 'success' THEN 1 ELSE 0 END) as success_count"),
                DB::raw("SUM(CASE WHEN res.status = 'failed' THEN 1 ELSE 0 END) as failed_count"),
                DB::raw("SUM(CASE WHEN res.status = 'pending' THEN 1 ELSE 0 END) as pending_count"),
                DB::raw('COUNT(res.id) as total')
            )
            ->groupBy('req.type')
            ->orderBy('req.type')
            ->get();
    }

}
