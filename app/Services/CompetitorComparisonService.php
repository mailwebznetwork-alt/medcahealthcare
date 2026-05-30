<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\CompetitorKeyword;
use App\Models\SiteKeywordRanking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompetitorComparisonService
{
    /**
     * Compare Medca vs competitor SERP positions for shared keywords.
     * Persists hijack_priority (1–10) on high-intent gaps where a competitor outranks us.
     *
     * @return Collection<int, array{
     *   competitor_keyword_id: int,
     *   keyword: string,
     *   competitor_id: int,
     *   competitor_name: string|null,
     *   intent_type: string,
     *   our_position: int,
     *   competitor_position: int,
     *   position_gap: int,
     *   hijack_priority: int
     * }>
     */
    public function identifyHighValueOpportunities(?int $competitorKeywordId = null): Collection
    {
        $ourRankings = $this->loadOurRankingIndex();
        $opportunities = collect();

        $query = CompetitorKeyword::query()
            ->with('competitor:id,name')
            ->where('is_active', true);

        if ($competitorKeywordId !== null) {
            $query->whereKey($competitorKeywordId);
        }

        $query->orderBy('id')->chunkById(100, function ($keywords) use ($ourRankings, $opportunities): void {
            foreach ($keywords as $keyword) {
                $result = $this->evaluateKeywordOpportunity($keyword, $ourRankings);
                if ($result !== null) {
                    $opportunities->push($result);
                }
            }
        });

        return $opportunities->sortByDesc('hijack_priority')->values();
    }

    public function hijackPriorityForKeyword(int $competitorKeywordId): ?int
    {
        $priority = CompetitorKeyword::query()->whereKey($competitorKeywordId)->value('hijack_priority');

        return $priority !== null ? (int) $priority : null;
    }

    /**
     * @param  Collection<string, int>  $ourRankings
     * @return array<string, mixed>|null
     */
    private function evaluateKeywordOpportunity(CompetitorKeyword $keyword, Collection $ourRankings): ?array
    {
        if (! $keyword->isHighIntent()) {
            if ($keyword->hijack_priority !== null) {
                $keyword->forceFill(['hijack_priority' => null])->save();
            }

            return null;
        }

        $competitorPosition = $keyword->latestPosition();
        $normalized = SiteKeywordRanking::normalizeKeyword($keyword->keyword);
        $ourPosition = $ourRankings->get($normalized);

        if ($competitorPosition === null || $ourPosition === null || $competitorPosition >= $ourPosition) {
            if ($keyword->hijack_priority !== null) {
                $keyword->forceFill(['hijack_priority' => null])->save();
            }

            return null;
        }

        $priority = $this->calculateHijackPriority($competitorPosition, $ourPosition, $keyword);
        $keyword->forceFill(['hijack_priority' => $priority])->save();

        return [
            'competitor_keyword_id' => $keyword->id,
            'keyword' => $keyword->keyword,
            'competitor_id' => (int) $keyword->competitor_id,
            'competitor_name' => $keyword->competitor?->name,
            'intent_type' => (string) $keyword->intent_type,
            'our_position' => $ourPosition,
            'competitor_position' => $competitorPosition,
            'position_gap' => $ourPosition - $competitorPosition,
            'hijack_priority' => $priority,
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function loadOurRankingIndex(): Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('site_keyword_rankings')) {
            return collect();
        }

        return SiteKeywordRanking::query()
            ->orderByDesc('recorded_date')
            ->orderByDesc('id')
            ->get(['keyword', 'position'])
            ->unique(fn (SiteKeywordRanking $row) => SiteKeywordRanking::normalizeKeyword($row->keyword))
            ->mapWithKeys(fn (SiteKeywordRanking $row) => [
                SiteKeywordRanking::normalizeKeyword($row->keyword) => (int) $row->position,
            ]);
    }

    private function calculateHijackPriority(int $competitorPosition, int $ourPosition, CompetitorKeyword $keyword): int
    {
        $gapScore = min(10, max(1, $ourPosition - $competitorPosition));

        $volumeBoost = 0;
        if ($keyword->search_volume !== null && $keyword->search_volume >= 1000) {
            $volumeBoost = 2;
        } elseif ($keyword->search_volume !== null && $keyword->search_volume >= 300) {
            $volumeBoost = 1;
        }

        $difficultyBoost = 0;
        if ($keyword->difficulty !== null && $keyword->difficulty <= 40) {
            $difficultyBoost = 1;
        }

        $intentBoost = strtolower((string) $keyword->intent_type) === 'local' ? 1 : 0;

        return max(1, min(10, $gapScore + $volumeBoost + $difficultyBoost + $intentBoost));
    }

    public function compareCompetitors(array $ids): Collection
    {
        return Competitor::whereIn('id', $ids)
            ->withCount(['keywords', 'leads'])
            ->withSum('trackings as total_clicks', 'clicks')
            ->get()
            ->map(function (Competitor $competitor) {
                $clicks = (int) $competitor->total_clicks;
                $conversions = $competitor->totalConversions();
                $rate = $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0.0;

                return [
                    'id' => $competitor->id,
                    'name' => $competitor->name,
                    'is_intercept_target' => $competitor->is_intercept_target,
                    'total_keywords' => $competitor->keywordsCount(),
                    'clicks' => $clicks,
                    'conversions' => $conversions,
                    'conversion_rate' => $rate,
                ];
            });
    }

    public function getBestPerformer(): ?array
    {
        return $this->getPerformerByConversionOrder('desc');
    }

    public function getWorstPerformer(): ?array
    {
        return $this->getPerformerByConversionOrder('asc');
    }

    public function getKeywordOverlap(array $ids): Collection
    {
        return DB::table('competitor_keywords')
            ->select('keyword', DB::raw('COUNT(DISTINCT competitor_id) as competitor_count'))
            ->whereIn('competitor_id', $ids)
            ->groupBy('keyword')
            ->having('competitor_count', '>', 1)
            ->orderByDesc('competitor_count')
            ->get();
    }

    private function getPerformerByConversionOrder(string $direction): ?array
    {
        $competitor = Competitor::active()
            ->withCount('leads')
            ->orderBy('leads_count', $direction)
            ->first();

        if (! $competitor) {
            return null;
        }

        return [
            'id' => $competitor->id,
            'name' => $competitor->name,
            'conversions' => $competitor->leads_count,
        ];
    }
}
