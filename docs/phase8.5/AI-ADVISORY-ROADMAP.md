# M. Future AI Advisory Roadmap (architecture only)

## Contract

`App\Contracts\Deployment\AiDeploymentAdvisoryInterface`

- `recommend(array $context): array`
- `supportedProviders(): array`

**Current binding:** `NullAiDeploymentAdvisory` (disabled).

## Planned providers (human approval required)

| Provider | Use case |
|----------|----------|
| AI Pulse | Internal recommendations from analytics |
| Gemini (`config('gemini.api_key')`) | Blueprint/style pack suggestions |
| ChatGPT | Content structure advisory |
| Cursor | Developer-assisted imports |

## Recommendation payload (future)

```json
{
  "blueprint_slug": "home_healthcare",
  "style_pack_slug": "healthcare_premium",
  "header_preset": "premium",
  "hero_style": "style_2",
  "cta_strategy": "consultation_first",
  "confidence": 0.82,
  "requires_approval": true
}
```

No auto-publish. Admin must click **Generate** or **Publish** explicitly.
