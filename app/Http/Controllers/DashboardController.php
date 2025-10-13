<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService) {
        $this->analyticsService = $analyticsService;
    }


    public function index() {

        $backends = $this->analyticsService->backendRequestAnalytics();
        $statusAnalytics = $this->analyticsService->backendRequestStatusAnalytics();
        $backendDurationType   = $this->analyticsService->backendRequestDurationByType();
        return view('dashboard' , compact('backends', 'statusAnalytics', 'backendDurationType'));
    }
}
