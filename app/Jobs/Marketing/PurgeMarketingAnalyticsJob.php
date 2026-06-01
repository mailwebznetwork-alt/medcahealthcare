<?php

namespace App\Jobs\Marketing;

use App\Services\Marketing\Retention\MarketingDataRetentionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PurgeMarketingAnalyticsJob implements ShouldQueue
{
    use Queueable;

    public function handle(MarketingDataRetentionService $retention): void
    {
        $retention->purgeClickEvents();
        $retention->purgeActivities();
    }
}
