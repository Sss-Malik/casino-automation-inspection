<?php

namespace App\Services;

use App\Models\AutomationResult;
use App\Models\BackendGames;
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


    /**
     * Request counts per backend, per request type.
     *
     * Aggregated in SQL on purpose: eager-loading every task (350k+ rows in
     * production) blew the 65k placeholder limit and the PHP memory limit.
     */
    public function backendRequestAnalytics()
    {
        $types = ['freeplay', 'recharge', 'create', 'read', 'reset-password', 'withdraw', 'read-backend'];

        $countsByBackend = DB::table('automation_results as res')
            ->join('automation_requests as req', 'req.task_id', '=', 'res.task_id')
            ->select('res.backend_id', 'req.type', DB::raw('COUNT(*) as total'))
            ->groupBy('res.backend_id', 'req.type')
            ->get()
            ->groupBy('backend_id');

        return BackendGames::whereNull('deleted_at')->get()->map(function ($game) use ($countsByBackend, $types) {
            $byType = ($countsByBackend[$game->id] ?? collect())->pluck('total', 'type');

            $row = [
                'game_name' => $game->name,
                'total_requests' => (int) $byType->sum(),
            ];
            foreach ($types as $type) {
                $row["{$type}_count"] = (int) ($byType[$type] ?? 0);
            }

            return $row;
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
