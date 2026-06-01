# Header Management (Patch 4)

## Location

**Settings → Appearance → Header** (`/settings/appearance`, Header tab)

## Architecture

- **Preset + configuration** — no drag-drop builder
- Presets: `config/theme_management.php` → `header_presets`
- Configuration toggles: `header_configuration_keys` stored in `theme_configurations.branding.header_config` (draft via `draft_branding`)

## Configurable items

| UI area | Storage |
|---------|---------|
| Logo, brand name, phone, WhatsApp, CTA | Appearance → **Branding** |
| Global tokens (`{{ company_name }}`) | Settings → **Global Content** |
| Top bar, search, location/branch selectors, social, secondary menu, mobile CTA/WhatsApp | Header → **configuration toggles** |
| Sticky behaviour | `sticky_behavior` key |
| Preset layout (Classic, Corporate, Premium, …) | `draft_header_preset` |

## Preview & publish

1. Save header draft
2. **Enable preview** (session) or open public site
3. **Publish** (admin / super_admin) to go live

Header inherits **Theme** tokens and **Style Pack** assignments on pages via existing `ThemeResolver` + `StylePackResolver`.

## Public rendering

`resources/views/global/header.blade.php` reads `ThemeResolver::headerConfiguration()` for top bar visibility and sticky CSS class modifiers.
