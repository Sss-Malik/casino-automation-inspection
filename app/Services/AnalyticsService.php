<?php

namespace App\Services;

use App\Models\BackendGames;
use App\Models\User;

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

}
