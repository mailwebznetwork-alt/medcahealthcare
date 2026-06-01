# Media Mapping (Patch 7)

## Location

**Site Architect → Block Studio** (`/site-architect/block-studio`) → **Media mapping** panel

## Slots

Defined per block family in `config/design_system.php` → `media_slots`:

| Family | Slots |
|--------|-------|
| Hero | desktop_image, mobile_image, video, fallback_image |
| Services | image, icon, badge |
| Testimonials | photo, company_logo |
| Gallery | desktop_gallery, mobile_gallery |

## Storage

- Primary: `blocks.settings_json.media`
- Page override: `pages.block_overrides_json[blockSlug].media`
- Package import: manifest `media_mapping` merged by `DeploymentPackageImporter`

## UI actions

Upload (public disk), path/URL text, remove, preview via Block Studio.

Runtime: `BlockSettingsResolver::renderVariables()` → `$blockMedia`.
