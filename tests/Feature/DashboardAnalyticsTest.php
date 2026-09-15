<?php

namespace Tests\Feature;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Tests\AutomationTestCase;

class DashboardAnalyticsTest extends AutomationTestCase
{
    public function test_backend_request_analytics_counts_requests_per_type_per_backend(): void
    {
        $juwa = $this->backend('juwa');
        $river = $this->backend('river');
        $this->backend('idle');

        $this->task(['backend_id' => $juwa], ['type' => 'read']);
        $this->task(['backend_id' => $juwa], ['type' => 'read']);
        $this->task(['backend_id' => $juwa], ['type' => 'recharge']);
        $this->task(['backend_id' => $river], ['type' => 'freeplay']);
        $this->task(['backend_id' => $river], ['type' => 'read-backend']);

        $rows = app(AnalyticsService::class)->backendRequestAnalytics()->keyBy('game_name');

        $this->assertSame(3, $rows['juwa']['total_requests']);
        $this->assertSame(2, $rows['juwa']['read_count']);
        $this->assertSame(1, $rows['juwa']['recharge_count']);
        $this->assertSame(0, $rows['juwa']['freeplay_count']);

        $this->assertSame(2, $rows['river']['total_requests']);
        $this->assertSame(1, $rows['river']['freeplay_count']);
        $this->assertSame(1, $rows['river']['read-backend_count']);

        $this->assertSame(0, $rows['idle']['total_requests']);
        $this->assertSame(0, $rows['idle']['read_count']);
    }

    public function test_backend_request_analytics_skips_soft_deleted_backends(): void
    {
        $this->backend('juwa');
        DB::table('backend_games')->insert(['name' => 'gone', 'deleted_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $names = app(AnalyticsService::class)->backendRequestAnalytics()->pluck('game_name')->all();

        $this->assertSame(['juwa'], $names);
    }

    public function test_backend_request_analytics_never_binds_one_placeholder_per_task(): void
    {
        // Production holds 353k results; eager-loading them produced
        // "1390 Prepared statement contains too many placeholders" and 512 MB
        // memory exhaustion, and the dashboard has returned 500 since 2026-03.
        $juwa = $this->backend('juwa');
        foreach (range(1, 70) as $_) {
            $this->task(['backend_id' => $juwa], ['type' => 'read']);
        }

        $maxBindings = 0;
        DB::listen(function ($query) use (&$maxBindings) {
            $maxBindings = max($maxBindings, count($query->bindings));
        });

        app(AnalyticsService::class)->backendRequestAnalytics();

        $this->assertLessThan(10, $maxBindings, 'analytics must aggregate in SQL, not load every task');
    }

    public function test_dashboard_renders_for_super_admin(): void
    {
        $juwa = $this->backend('juwa');
        $this->task(['backend_id' => $juwa, 'duration_seconds' => 2.5], ['type' => 'read']);

        $this->actingAs($this->superAdmin())->get('/dashboard')->assertOk();
    }
}
