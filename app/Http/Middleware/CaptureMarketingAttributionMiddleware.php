<?php

namespace App\Http\Middleware;

use App\Services\Marketing\Attribution\UtmCaptureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttributionMiddleware
{
    public function __construct(
        private readonly UtmCaptureService $utmCapture,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (config('marketing_automation.enabled', true) && config('marketing_automation.attribution.enabled', true)) {
            $this->utmCapture->captureFromRequest($request);
        }

        return $next($request);
    }
}
