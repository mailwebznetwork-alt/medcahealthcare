<?php

namespace App\Services\Marketing\Analytics;

use App\Models\Lead;
use App\Models\MarketingConversionEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class MarketingConversionMetricsService
{
    /**
     * @return array<string, float|int>
     */
    public function metrics(?Carbon $from = null, ?Carbon $to = null): array
    {
        $leadQuery = Lead::query();
        if ($from) {
            $leadQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $leadQuery->where('created_at', '<=', $to);
        }

        $total = (clone $leadQuery)->count();
        $converted = (clone $leadQuery)->whereNotNull('converted_at')->count();

        $stageCounts = [];
        if (Schema::hasTable('marketing_conversion_events')) {
            $convQuery = MarketingConversionEvent::query();
            if ($from) {
                $convQuery->where('converted_at', '>=', $from);
            }
            if ($to) {
                $convQuery->where('converted_at', '<=', $to);
            }

            foreach (['lead_created', 'assessment_scheduled', 'proposal_sent', 'client_converted'] as $type) {
                $stageCounts[$type] = (clone $convQuery)->where('conversion_type', $type)->count();
            }
        }

        $velocity = $this->leadVelocity($from, $to);
        $timeToConvert = $this->averageTimeToConversion($from, $to);

        return [
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0.0,
            'stage_counts' => $stageCounts,
            'lead_velocity_per_day' => $velocity,
            'avg_time_to_conversion_hours' => $timeToConvert,
            'stage_conversion_assessment_pct' => $this->stageRate($stageCounts['lead_created'] ?? $total, $stageCounts['assessment_scheduled'] ?? 0),
            'stage_conversion_proposal_pct' => $this->stageRate($stageCounts['assessment_scheduled'] ?? 0, $stageCounts['proposal_sent'] ?? 0),
            'stage_conversion_client_pct' => $this->stageRate($stageCounts['proposal_sent'] ?? 0, $stageCounts['client_converted'] ?? 0),
        ];
    }

    private function leadVelocity(?Carbon $from, ?Carbon $to): float
    {
        $from = $from ?? now()->subDays(30);
        $to = $to ?? now();
        $days = max(1, $from->diffInDays($to));

        return round(Lead::query()->whereBetween('created_at', [$from, $to])->count() / $days, 2);
    }

    private function averageTimeToConversion(?Carbon $from, ?Carbon $to): ?float
    {
        $query = Lead::query()->whereNotNull('converted_at');
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $leads = $query->get(['created_at', 'converted_at']);
        if ($leads->isEmpty()) {
            return null;
        }

        $hours = $leads->avg(fn (Lead $lead): float => $lead->created_at->diffInMinutes($lead->converted_at) / 60);

        return round((float) $hours, 2);
    }

    private function stageRate(int $fromCount, int $toCount): float
    {
        return $fromCount > 0 ? round(($toCount / $fromCount) * 100, 2) : 0.0;
    }
}
