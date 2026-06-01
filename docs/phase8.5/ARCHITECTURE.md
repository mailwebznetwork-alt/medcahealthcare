# A. Deployment Engine Architecture

## Layers

| Layer | Responsibility | Storage |
|-------|----------------|---------|
| **Theme** | Colors, typography, header/layout presets, shape tokens | `theme_configurations`, Phase 8 |
| **Style Pack** | Global assignments (hero→style_2, carousel→style_3) | `config/style_packs.php`, `active_style_pack` |
| **Blueprint** | Page list, block order, per-page overrides | `config/blueprints.php`, `pages.deployment_meta_json` |
| **Pages** | Ordered `{{block:slug}}` tokens | `pages.content` |
| **Blocks** | Blade code + `settings_json` | `blocks` table |
| **ContentParser** | Single renderer | unchanged |

## Services

- `App\Services\Deployment\BlueprintRegistry`
- `App\Services\Deployment\BlueprintPageGenerator`
- `App\Services\Deployment\StylePackRegistry` / `StylePackResolver`
- `App\Services\Deployment\BlockSettingsResolver`
- `App\Services\Deployment\BlockPresetRepository`
- `App\Services\Theme\ThemeTokenRegistry` (shape token catalog)

## Public render flow

1. `CmsPageController` sets `ContentRenderContext` (page vars + style pack + `currentPage`).
2. `ContentParser::renderBlock` merges `settings_json` + page `block_overrides_json` + style pack.
3. `ThemeResolver::publicCssBlock` injects color + shape CSS variables.

## AI advisory (Step 14)

`App\Contracts\Deployment\AiDeploymentAdvisoryInterface` → `NullAiDeploymentAdvisory` (no execution).
