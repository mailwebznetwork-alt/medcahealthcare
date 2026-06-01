# C. Style Pack Documentation

**Config:** `config/style_packs.php` (10 packs)

Examples: `healthcare_professional`, `healthcare_premium`, `luxury_black`, `consultancy_corporate`.

Each pack defines:

- `theme_preset_slug` — links to Phase 8 `ThemePreset`
- `header_preset` / `layout_preset`
- `assignments` — maps design families (`hero`, `services`, `cta`, …) to `style_1`…`style_5`

**Resolver:** `StylePackResolver::activeSlug()` reads:

1. Session preview key `deployment_preview_style_pack`
2. Page `deployment_meta_json.style_pack`
3. `theme_configurations.active_style_pack` (draft when theme preview on)

**One-click transformation:** Apply style pack to draft theme + regenerate pages, or set `active_style_pack` and publish theme.
