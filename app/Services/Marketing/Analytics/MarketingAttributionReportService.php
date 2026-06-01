<?php

namespace App\Services\Marketing\Analytics;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketingAttributionReportService
{
    /**
     * @return array{first_touch: array<string, int>, last_touch: array<string, int>}
     */
    public function compare(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = Lead::query();
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'first_touch' => (clone $query)
                ->select('first_touch_source as source', DB::raw('count(*) as total'))
                ->whereNotNull('first_touch_source')
                ->groupBy('first_touch_source')
                ->orderByDesc('total')
                ->limit(15)
                ->pluck('total', 'source')
                ->map(fn ($v) => (int) $v)
                ->all(),
            'last_touch' => (clone $query)
                ->select('last_touch_source as source', DB::raw('count(*) as total'))
                ->whereNotNull('last_touch_source')
                ->groupBy('last_touch_source')
                ->orderByDesc('total')
                ->limit(15)
                ->pluck('total', 'source')
                ->map(fn ($v) => (int) $v)
                ->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function gbpAttribution(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30);
        $to = $to ?? now();

        $leadQuery = Lead::query()->whereBetween('created_at', [$from, $to]);

        return [
            'gbp_leads' => (clone $leadQuery)->where(function ($q): void {
                $q->where('source', 'gmb')
                    ->orWhere('utm_source', 'like', '%gbp%')
                    ->orWhere('utm_source', 'like', '%gmb%');
            })->count(),
            'gbp_website_clicks' => $this->clickCount('gbp_website_visit', $from, $to),
            'gbp_call_clicks' => $this->clickCount('gbp_call_click', $from, $to),
            'gbp_whatsapp_clicks' => $this->clickCount('gbp_whatsapp_click', $from, $to),
        ];
    }

    private function clickCount(string $type, Carbon $from, Carbon $to): int
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('marketing_click_events')) {
            return 0;
        }

        return \App\Models\MarketingClickEvent::query()
            ->where('event_type', $type)
            ->whereBetween('occurred_at', [$from, $to])
            ->count();
    }
}
