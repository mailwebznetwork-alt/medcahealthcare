<?php

namespace App\Contracts\Deployment;

/**
 * Phase 8.5 Step 14 — architecture only (no AI execution).
 *
 * Future: AI Pulse / Gemini / ChatGPT / Cursor recommend blueprints and style packs.
 * All recommendations require human approval before generate/publish.
 */
interface AiDeploymentAdvisoryInterface
{
    /**
     * @param  array<string, mixed>  $context  industry, goals, existing pages, brand notes
     * @return array<string, mixed>  recommendation payload (blueprint, style_pack, header, etc.)
     */
    public function recommend(array $context): array;

    /**
     * @return list<string>  advisory provider identifiers
     */
    public function supportedProviders(): array;
}
