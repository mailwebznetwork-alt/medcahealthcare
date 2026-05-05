<?php

namespace App\Services\Growth;

use App\Models\Competitor;
use App\Models\CompetitorKeyword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompetitorComparisonService
{
    public function compareCompetitors(array $ids): array
    {
        sort($ids);
        $cacheKey = 'competitors:compare:'.implode(',', $ids);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ids): array {
            $competitors = Competitor::query()
                ->whereIn('id', $ids)
                ->withCount('keywords')
                ->get();

            if ($competitors->isEmpty()) {
                return [];
            }

            $aggregateRows = DB::table('competitors')
                ->leftJoin('competitor_keywords', 'competitor_keywords.competitor_id', '=', 'competitors.id')
                ->leftJoin('competitor_trackings', 'competitor_trackings.competitor_keyword_id', '=', 'competitor_keywords.id')
                ->leftJoin('competitor_leads', 'competitor_leads.competitor_keyword_id', '=', 'competitor_keywords.id')
                ->whereIn('competitors.id', $ids)
                ->groupBy('competitors.id')
                ->selectRaw('competitors.id as competitor_id')
                ->selectRaw('COALESCE(SUM(competitor_trackings.clicks), 0) as total_clicks')
                ->selectRaw("COALESCE(SUM(CASE WHEN competitor_leads.status = 'converted' THEN 1 ELSE 0 END), 0) as conversions")
                ->get()
                ->keyBy('competitor_id');

            return $competitors->map(function (Competitor $competitor) use ($aggregateRows): array {
                $aggregate = $aggregateRows->get($competitor->id);
                $clicks = (int) ($aggregate->total_clicks ?? 0);
                $conversions = (int) ($aggregate->conversions ?? 0);

                return [
                    'id' => $competitor->id,
                    'name' => $competitor->name,
                    'total_keywords' => (int) $competitor->keywords_count,
                    'total_clicks' => $clicks,
                    'conversions' => $conversions,
                    'conversion_rate' => $clicks > 0 ? round($conversions / $clicks, 4) : 0.0,
                ];
            })->values()->all();
        });
    }

    public function getBestPerformer(): ?array
    {
        $allIds = Competitor::query()->pluck('id')->all();
        if ($allIds === []) {
            return null;
        }

        $comparison = $this->compareCompetitors($allIds);
        if ($comparison === []) {
            return null;
        }

        usort($comparison, function (array $a, array $b): int {
            return ($b['conversion_rate'] <=> $a['conversion_rate']) ?: ($b['conversions'] <=> $a['conversions']);
        });

        return $comparison[0] ?? null;
    }

    public function getWorstPerformer(): ?array
    {
        $allIds = Competitor::query()->pluck('id')->all();
        if ($allIds === []) {
            return null;
        }

        $comparison = $this->compareCompetitors($allIds);
        if ($comparison === []) {
            return null;
        }

        usort($comparison, function (array $a, array $b): int {
            return ($a['conversion_rate'] <=> $b['conversion_rate']) ?: ($a['conversions'] <=> $b['conversions']);
        });

        return $comparison[0] ?? null;
    }

    public function getKeywordOverlap(array $ids): array
    {
        sort($ids);
        $cacheKey = 'competitors:keyword-overlap:'.implode(',', $ids);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ids): array {
            return CompetitorKeyword::query()
                ->join('competitors', 'competitors.id', '=', 'competitor_keywords.competitor_id')
                ->whereIn('competitor_keywords.competitor_id', $ids)
                ->groupBy('competitor_keywords.keyword')
                ->havingRaw('COUNT(DISTINCT competitor_keywords.competitor_id) > 1')
                ->selectRaw('competitor_keywords.keyword')
                ->selectRaw('COUNT(DISTINCT competitor_keywords.competitor_id) as competitor_count')
                ->selectRaw('GROUP_CONCAT(DISTINCT competitors.name) as competitors')
                ->orderByDesc('competitor_count')
                ->orderBy('competitor_keywords.keyword')
                ->get()
                ->map(fn (object $row): array => [
                    'keyword' => (string) $row->keyword,
                    'competitor_count' => (int) $row->competitor_count,
                    'competitors' => array_filter(array_map('trim', explode(',', (string) $row->competitors))),
                ])
                ->all();
        });
    }
}
