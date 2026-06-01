<?php

namespace App\Services\Marketing\Attribution;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadAttributionService
{
    public function __construct(
        private readonly UtmCaptureService $utmCapture,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function applyToLead(Lead $lead, array $validated, Request $request): void
    {
        if (! config('marketing_automation.enabled', true)) {
            return;
        }

        $attribution = $this->utmCapture->mergeForLead($validated, $request);
        if ($attribution !== []) {
            $lead->fill($attribution);
        }
    }
}
