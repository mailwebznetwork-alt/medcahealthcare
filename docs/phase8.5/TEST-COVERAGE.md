# K. Test Coverage Report

## Phase 8.5 tests

**File:** `tests/Feature/DeploymentEngineTest.php`

| Test | Covers |
|------|--------|
| Lists blueprints | BlueprintRegistry |
| Generates pages | BlueprintPageGenerator, Page tokens |
| Resolves style variant | BlockSettingsResolver + StylePack |
| Maps shape tokens | ThemeTokenRegistry, ThemeCssVariableBuilder |
| Policy roles | DeploymentEnginePolicy |
| Route access | Blueprint Builder HTTP |

**Run:** `php artisan test --filter=DeploymentEngine`

## Regression

Full suite: **317 tests** (includes Phase 8 theme tests + existing Site Architect tests).

## Not yet automated (UI / visual)

- Appearance shape token tab UI
- Block preset Livewire UI
- Carousel blade wiring per style_1–5
- Header mega/transparent visual QA
