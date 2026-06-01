<?php

namespace App\Services\Deployment;

use App\Contracts\Deployment\AiDeploymentAdvisoryInterface;

/**
 * Stub until AI Pulse / Gemini integration is approved for deployment advisory.
 */
class NullAiDeploymentAdvisory implements AiDeploymentAdvisoryInterface
{
    public function recommend(array $context): array
    {
        return [
            'status' => 'disabled',
            'message' => __('AI deployment advisory is not enabled. Select blueprint and style pack manually.'),
            'context_keys' => array_keys($context),
        ];
    }

    public function supportedProviders(): array
    {
        return [];
    }
}
