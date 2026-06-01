# Section Design Controls (Patch 6)

## Location

**Site Architect → Block Studio** (`/site-architect/block-studio`) → **Section controls** panel

## Schema

Keys from `config/design_system.php` → `section_controls`:

- Background: color, image, gradient, overlay, pattern, shape divider
- Layout: spacing, padding, border radius, shadow, animation
- Visibility: desktop, tablet, mobile (boolean)

Stored in `blocks.settings_json.section` (page overrides in `pages.block_overrides_json`).

## Workflow

1. Select block slug
2. Edit section fields
3. **Preview** (renders via `ContentParser`)
4. **Save to block** (persists `settings_json`)

No second renderer — variables exposed as `$blockSection` in block Blade when templates consume them.
