<?php

namespace App\Observers;

use App\Jobs\AnalyzeHijackOpportunityJob;
use App\Models\CompetitorTracking;
use App\Services\CompetitorComparisonService;

class CompetitorTrackingObserver
{
    public function created(CompetitorTracking $tracking): void
    {
        $keywordId = (int) $tracking->competitor_keyword_id;

        $previous = CompetitorTracking::query()
            ->where('competitor_keyword_id', $keywordId)
            ->where('id', '!=', $tracking->id)
            ->orderByDesc('recorded_date')
            ->orderByDesc('id')
            ->first();

        $competitorImproved = $tracking->position !== null
            && $previous?->position !== null
            && (int) $tracking->position < (int) $previous->position;

        if (! $competitorImproved) {
            return;
        }

        $this->evaluateAndDispatch($keywordId);
    }

    private function evaluateAndDispatch(int $competitorKeywordId): void
    {
        $service = app(CompetitorComparisonService::class);
        $service->identifyHighValueOpportunities($competitorKeywordId);

        $priority = $service->hijackPriorityForKeyword($competitorKeywordId);
        if ($priority !== null && $priority >= 1) {
            AnalyzeHijackOpportunityJob::dispatch($competitorKeywordId);
        }
    }
}
