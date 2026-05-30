<?php

namespace App\Jobs;

use App\Services\Growth\BacklinkMonitorService;
use App\Support\GrowthReadinessReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshBacklinkIntelligenceJob implements ShouldQueue
{
    use Queueable;

    public function handle(BacklinkMonitorService $monitor): void
    {
        $monitor->refreshAll();
        GrowthReadinessReport::forget();
    }
}
