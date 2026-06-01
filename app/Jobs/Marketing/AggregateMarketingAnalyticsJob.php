<?php

namespace App\Jobs\Marketing;

use App\Services\Marketing\Analytics\MarketingAnalyticsAggregator;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AggregateMarketingAnalyticsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $date = null,
    ) {}

    public function handle(MarketingAnalyticsAggregator $aggregator): void
    {
        $date = $this->date ? Carbon::parse($this->date) : now()->subDay();
        $aggregator->aggregateDaily($date);
    }
}
