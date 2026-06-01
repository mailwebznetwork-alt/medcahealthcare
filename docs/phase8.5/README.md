# Phase 8.5 — MarkOnMinds Deployment Engine

**Status:** Foundation + Completion Patch (admin UIs for header, presets, block studio, globals, sections, packages)  
**Date:** 2026-05-31

## Objective

Transform the platform into a reusable **Deployment Engine**: Brand + Content + Images + Theme + Style Pack + Blueprint → generated **normal Pages and Blocks**, fully editable in Site Architect after generation.

## Architecture (Deliverable A)

```
Theme (Phase 8)
    └── Style Pack (global block/header assignments)
            └── Blueprint (page structure + block order + overrides)
                    └── Pages (content = {{block:slug}} tokens)
                            └── Blocks (code + settings_json)
                                    └── ContentParser (unchanged engine)
```

**Not built:** second CMS, second renderer, duplicate blocks.

## Documentation index

| Doc | Topic |
|-----|--------|
| [ARCHITECTURE.md](./ARCHITECTURE.md) | Deployment Engine layers |
| [BLUEPRINT-ENGINE.md](./BLUEPRINT-ENGINE.md) | Blueprint config + generator |
| [STYLE-PACKS.md](./STYLE-PACKS.md) | Global style assignments |
| [HEADER-SYSTEM.md](./HEADER-SYSTEM.md) | Preset + configuration (Phase 8.5 extension) |
| [CAROUSEL-SYSTEM.md](./CAROUSEL-SYSTEM.md) | Carousel style modifiers |
| [MEDIA-MAPPING.md](./MEDIA-MAPPING.md) | Block settings_json media slots |
| [BLOCK-PRESETS.md](./BLOCK-PRESETS.md) | Save/apply/export block presets |
| [THEME-EXPANSION.md](./THEME-EXPANSION.md) | Radius/shadow/spacing tokens |
| [SECURITY-REVIEW.md](./SECURITY-REVIEW.md) | Roles + validation |
| [PERFORMANCE-REVIEW.md](./PERFORMANCE-REVIEW.md) | No duplicate engines |
| [TEST-COVERAGE.md](./TEST-COVERAGE.md) | Pest coverage (foundation) |
| [TEST-COVERAGE-COMPLETION.md](./TEST-COVERAGE-COMPLETION.md) | Completion patch tests |
| [GLOBAL-CONTENT-VARIABLES.md](./GLOBAL-CONTENT-VARIABLES.md) | Global tokens admin |
| [SECTION-LIBRARY.md](./SECTION-LIBRARY.md) | Reusable sections |
| [DEPLOYMENT-PACKAGES.md](./DEPLOYMENT-PACKAGES.md) | Export/import packages |
| [SECTION-CONTROLS.md](./SECTION-CONTROLS.md) | Per-block section design |
| [DEPLOYMENT-ENGINE-SCORECARD.md](./DEPLOYMENT-ENGINE-SCORECARD.md) | Final readiness scorecard |
| [ADMIN-USER-GUIDE.md](./ADMIN-USER-GUIDE.md) | Blueprint Builder workflow |
| [AI-ADVISORY-ROADMAP.md](./AI-ADVISORY-ROADMAP.md) | Future AI (architecture only) |

## Admin entry points

| Area | URL |
|------|-----|
| Deployment workflow hub | Any Site Architect deployment page (hub strip) |
| Blueprint Builder | `/site-architect/blueprint-builder` |
| Section Library | `/site-architect/section-library` |
| Block Presets | `/site-architect/block-presets` |
| Block Studio (media + section) | `/site-architect/block-studio` |
| Deployment Packages | `/site-architect/deployment-packages` |
| Header + theme | `/settings/appearance` → Header |
| Global content | `/settings/global-content` |

Workflow: Theme → Style Pack → Blueprint → Header → Sections/Presets → **Generate pages** → Block Studio → **Package export** for new clients.

## Migrations

`2026_05_31_120000_create_deployment_engine_tables.php`

- `blocks.settings_json`
- `pages.block_overrides_json`, `pages.deployment_meta_json`
- `theme_configurations.published_shape`, `draft_shape`, `active_style_pack`, `draft_style_pack`
- `block_presets`, `deployment_generations`

## Migrations (supplemental + completion)

- `2026_05_31_140000_phase85_supplemental_patch.php` — globals, sections, packages
- `2026_05_31_160000_phase85_completion_patch.php` — global content version snapshots

## Tests

- `tests/Feature/DeploymentEngineTest.php`
- `tests/Feature/Phase85SupplementalPatchTest.php`
- `tests/Feature/Phase85CompletionPatchTest.php`

Run: `php artisan test --filter=Phase85`
