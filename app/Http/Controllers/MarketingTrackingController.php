<?php

namespace App\Http\Controllers;

use App\Services\Marketing\Tracking\MarketingClickTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingTrackingController extends Controller
{
    public function __construct(
        private readonly MarketingClickTrackingService $trackingService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        if (! config('marketing_automation.enabled', true) || ! config('marketing_automation.click_tracking.enabled', true)) {
            return response()->json(['recorded' => false]);
        }

        $result = $this->trackingService->record($request);

        return response()->json($result);
    }
}
