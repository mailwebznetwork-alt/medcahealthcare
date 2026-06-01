<?php

namespace App\Livewire\Marketing;

use App\Models\MarketingSetting;
use App\Services\Marketing\Analytics\MarketingAnalyticsAggregator;
use App\Services\Marketing\Analytics\MarketingAttributionReportService;
use App\Services\Marketing\Analytics\MarketingConversionMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class IntelligenceDashboard extends Component
{
    use AuthorizesRequests;

    public string $tab = 'executive';

    public string $trendGranularity = 'daily';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->authorize('view', MarketingSetting::current());
        $this->dateFrom = now()->subDays(30)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['executive', 'whatsapp', 'calls', 'attribution', 'conversions', 'reporting'], true)) {
            $this->tab = $tab;
        }
    }

    public function render(
        MarketingAnalyticsAggregator $aggregator,
        MarketingAttributionReportService $attributionReport,
        MarketingConversionMetricsService $conversionMetrics,
    ): View {
        $from = $this->dateFrom !== '' ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to = $this->dateTo !== '' ? Carbon::parse($this->dateTo)->endOfDay() : null;

        return view('livewire.marketing.intelligence-dashboard', [
            'executive' => $aggregator->executiveSummary($from, $to),
            'whatsapp' => $aggregator->whatsAppMetrics(),
            'calls' => $aggregator->callMetrics(),
            'attribution' => $attributionReport->compare($from, $to),
            'gbp' => $attributionReport->gbpAttribution($from, $to),
            'conversions' => $conversionMetrics->metrics($from, $to),
            'trend' => $aggregator->leadTrend($this->trendGranularity),
        ]);
    }
}
