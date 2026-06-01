<?php

namespace App\Services\Marketing\Analytics;

use App\Enums\LeadPipelineStage;
use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\MarketingAnalyticsDailyStat;
use App\Models\MarketingClickEvent;
use App\Models\MarketingConversionEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketingAnalyticsAggregator
{
    public function executiveSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $cacheKey = 'marketing.executive.'.($from?->toDateString() ?? 'all').'.'.($to?->toDateString() ?? 'all');

        return Cache::remember($cacheKey, config('marketing_automation.analytics.cache_ttl', 900), function () use ($from, $to): array {
            $leadQuery = Lead::query();
            if ($from) {
                $leadQuery->where('created_at', '>=', $from);
            }
            if ($to) {
                $leadQuery->where('created_at', '<=', $to);
            }

            $total = (clone $leadQuery)->count();
            $converted = (clone $leadQuery)->where(function ($q): void {
                $q->whereIn('pipeline_stage', [
                    LeadPipelineStage::Converted->value,
                    LeadPipelineStage::ActiveClient->value,
                ])->orWhere('status', 'converted');
            })->count();
            $lost = (clone $leadQuery)->where(function ($q): void {
                $q->where('pipeline_stage', LeadPipelineStage::Lost->value)
                    ->orWhere('status', 'closed');
            })->count();
            $qualified = (clone $leadQuery)->whereNotIn('pipeline_stage', [
                LeadPipelineStage::NewLead->value,
                LeadPipelineStage::Lost->value,
            ])->count();

            return [
                'total_leads' => $total,
                'qualified_leads' => $qualified,
                'converted_leads' => $converted,
                'lost_leads' => $lost,
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0.0,
                'whatsapp_leads' => (clone $leadQuery)->where('source', LeadSource::WhatsApp->value)->count(),
                'call_leads' => (clone $leadQuery)->where('source', LeadSource::Call->value)->count(),
                'top_sources' => $this->topDimension($leadQuery, 'source'),
                'top_campaigns' => $this->topDimension($leadQuery, 'utm_campaign'),
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    public function whatsAppMetrics(string $period = 'month'): array
    {
        if (! Schema::hasTable('marketing_click_events')) {
            return ['today' => 0, 'week' => 0, 'month' => 0];
        }

        $base = MarketingClickEvent::query()->where('event_type', 'whatsapp_click');

        return [
            'today' => (clone $base)->where('occurred_at', '>=', now()->startOfDay())->count(),
            'week' => (clone $base)->where('occurred_at', '>=', now()->startOfWeek())->count(),
            'month' => (clone $base)->where('occurred_at', '>=', now()->startOfMonth())->count(),
            'by_source' => (clone $base)->where('occurred_at', '>=', now()->startOfMonth())
                ->select('source', DB::raw('count(*) as total'))
                ->groupBy('source')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'source')
                ->all(),
            'top_pages' => (clone $base)->where('occurred_at', '>=', now()->startOfMonth())
                ->select('page_path', DB::raw('count(*) as total'))
                ->groupBy('page_path')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'page_path')
                ->all(),
        ];
    }

    /**
     * @return array<string, int|array<string, int>>
     */
    public function callMetrics(string $period = 'month'): array
    {
        if (! Schema::hasTable('marketing_click_events')) {
            return ['today' => 0, 'week' => 0, 'month' => 0];
        }

        $base = MarketingClickEvent::query()->where('event_type', 'phone_click');

        return [
            'today' => (clone $base)->where('occurred_at', '>=', now()->startOfDay())->count(),
            'week' => (clone $base)->where('occurred_at', '>=', now()->startOfWeek())->count(),
            'month' => (clone $base)->where('occurred_at', '>=', now()->startOfMonth())->count(),
            'mobile' => (clone $base)->where('device_type', 'mobile')->where('occurred_at', '>=', now()->startOfMonth())->count(),
            'desktop' => (clone $base)->where('device_type', 'desktop')->where('occurred_at', '>=', now()->startOfMonth())->count(),
            'by_source' => (clone $base)->where('occurred_at', '>=', now()->startOfMonth())
                ->select('source', DB::raw('count(*) as total'))
                ->groupBy('source')
                ->pluck('total', 'source')
                ->all(),
        ];
    }

    /**
     * @return list<array{date: string, total: int}>
     */
    public function leadTrend(string $granularity = 'daily', int $points = 30): array
    {
        $from = match ($granularity) {
            'weekly' => now()->subWeeks($points)->startOfWeek(),
            'monthly' => now()->subMonths($points)->startOfMonth(),
            'quarterly' => now()->subQuarters($points)->startOfQuarter(),
            default => now()->subDays($points)->startOfDay(),
        };

        $leads = Lead::query()
            ->where('created_at', '>=', $from)
            ->get(['created_at']);

        $bucketFn = match ($granularity) {
            'weekly' => fn ($date) => $date->format('o-\WW'),
            'monthly' => fn ($date) => $date->format('Y-m'),
            'quarterly' => fn ($date) => $date->format('Y-\QQ'),
            default => fn ($date) => $date->format('Y-m-d'),
        };

        return $leads
            ->groupBy(fn (Lead $lead) => $bucketFn($lead->created_at))
            ->map(fn ($group, string $bucket): array => ['date' => $bucket, 'total' => $group->count()])
            ->sortKeys()
            ->values()
            ->all();
    }

    public function aggregateDaily(Carbon $date): void
    {
        if (! Schema::hasTable('marketing_analytics_daily_stats')) {
            return;
        }

        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $leadCount = Lead::query()->whereBetween('created_at', [$start, $end])->count();
        $this->upsertStat($date, 'leads', 'total', $leadCount);

        foreach (LeadSource::cases() as $source) {
            $count = Lead::query()->whereBetween('created_at', [$start, $end])->where('source', $source->value)->count();
            $this->upsertStat($date, 'leads_by_source', $source->value, $count);
        }

        if (Schema::hasTable('marketing_click_events')) {
            foreach (['whatsapp_click', 'phone_click', 'cta_click'] as $event) {
                $count = MarketingClickEvent::query()
                    ->where('event_type', $event)
                    ->whereBetween('occurred_at', [$start, $end])
                    ->count();
                $this->upsertStat($date, 'clicks', $event, $count);
            }
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Lead>  $query
     * @return array<string, int>
     */
    private function topDimension($query, string $column): array
    {
        if (! Schema::hasColumn('leads', $column)) {
            return [];
        }

        return (clone $query)
            ->select($column, DB::raw('count(*) as total'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', $column)
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function upsertStat(Carbon $date, string $group, string $key, int $value): void
    {
        MarketingAnalyticsDailyStat::query()->updateOrCreate(
            [
                'stat_date' => $date->toDateString(),
                'metric_group' => $group,
                'metric_key' => $key,
            ],
            ['metric_value' => $value]
        );
    }
}
