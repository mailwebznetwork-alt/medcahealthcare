<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Growth\BulkStoreCompetitorRequest;
use App\Http\Requests\Growth\CompareCompetitorsRequest;
use App\Models\Competitor;
use App\Models\CompetitorKeyword;
use App\Services\Growth\CompetitorComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompetitorController extends Controller
{
    public function __construct(private readonly CompetitorComparisonService $comparisonService) {}

    public function index(): JsonResponse
    {
        $competitors = Competitor::query()
            ->withCount([
                'keywords',
                'leads',
            ])
            ->orderByDesc('is_intercept_target')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $competitors,
        ]);
    }

    public function bulkStore(BulkStoreCompetitorRequest $request): JsonResponse
    {
        $stored = [];
        $payload = $request->validated('competitors');

        DB::transaction(function () use ($payload, &$stored): void {
            foreach ($payload as $row) {
                $name = Str::of(strip_tags((string) $row['name']))->trim()->toString();
                $website = isset($row['website']) ? Str::of(strip_tags((string) $row['website']))->trim()->toString() : null;

                $competitor = Competitor::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'website' => $website !== '' ? $website : null,
                        'is_active' => (bool) ($row['is_active'] ?? true),
                        'is_intercept_target' => (bool) ($row['is_intercept_target'] ?? false),
                    ]
                );

                $stored[] = [
                    'id' => $competitor->id,
                    'name' => $competitor->name,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'data' => [
                'count' => count($stored),
                'competitors' => $stored,
            ],
        ]);
    }

    public function compare(CompareCompetitorsRequest $request): JsonResponse
    {
        $ids = $request->validated('competitor_ids');
        $comparison = $this->comparisonService->compareCompetitors($ids);
        $overlap = $this->comparisonService->getKeywordOverlap($ids);

        return response()->json([
            'success' => true,
            'data' => [
                'comparison' => $comparison,
                'keyword_overlap' => $overlap,
            ],
        ]);
    }

    public function summary(): JsonResponse
    {
        $totalCompetitors = Competitor::query()->count();
        $activeCompetitors = Competitor::query()->active()->count();
        $totalKeywords = CompetitorKeyword::query()->count();
        $totalConversions = DB::table('competitor_leads')
            ->where('status', 'converted')
            ->count();

        $bestPerformer = $this->comparisonService->getBestPerformer();
        $worstPerformer = $this->comparisonService->getWorstPerformer();

        $funnelInsight = null;
        $scaleInsight = null;
        if ($bestPerformer !== null && $worstPerformer !== null) {
            if ($worstPerformer['total_clicks'] > 0 && $worstPerformer['conversion_rate'] <= 0.02) {
                $funnelInsight = [
                    'competitor' => $worstPerformer['name'],
                    'note' => 'High clicks and low conversion indicate weak funnel.',
                ];
            }

            if ($bestPerformer['total_clicks'] > 0 && $bestPerformer['conversion_rate'] >= 0.1) {
                $scaleInsight = [
                    'competitor' => $bestPerformer['name'],
                    'note' => 'Low-to-medium clicks and high conversion indicate scale opportunity.',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_competitors' => $totalCompetitors,
                'active_competitors' => $activeCompetitors,
                'total_keywords' => $totalKeywords,
                'total_conversions' => $totalConversions,
                'best_competitor' => $bestPerformer,
                'worst_competitor' => $worstPerformer,
                'insights' => [
                    'weak_funnel' => $funnelInsight,
                    'scale_opportunity' => $scaleInsight,
                ],
            ],
        ]);
    }
}
