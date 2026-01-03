<?php

namespace App\Services;

use App\Models\AutomationResult;
use App\Models\BackendGames;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function backendRequestDurationByType()
    {
        return AutomationResult::join('automation_requests', 'automation_requests.task_id', '=', 'automation_results.task_id')
            ->join('backend_games', 'backend_games.id', '=', 'automation_results.backend_id')
            ->select(
                'backend_games.name as game_name',
                'automation_requests.type',
                DB::raw('ROUND(AVG(automation_results.duration_seconds), 2) as avg_duration')
            )
            ->groupBy('backend_games.name', 'automation_requests.type')
            ->orderBy('backend_games.name')
            ->get();
    }


    public function providerAnalytics($startDate = null, $endDate = null)
    {
        // Default to 'Start of Month' -> 'Today' if no dates provided
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()   : Carbon::now()->endOfDay();

        return DB::table('wallet_detail')
            ->select(
                'provider',
                DB::raw('SUM(amount_minor) as total_amount'),
                DB::raw('COUNT(*) as total_transactions')
            )
            ->where('status', 'finished')
            ->where('type', 'DEPOSIT')
            ->whereIn('provider', ['stripe', 'paypal', 'chime', 'nowpayments', 'speed', 'manual_admin'])
            // Apply Date Filtering
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('provider')
            ->get();

    }

    public function frequencyAnalytics() {
        return DB::table('automation_results')
            ->select(
            DB::raw("DATE(created_at) as date"),
            DB::raw("COUNT(*) as count")
        )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }


    public function statusAnalytics() {
        return DB::table('automation_results')->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
    }


}
