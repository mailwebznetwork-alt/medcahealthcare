# Block Presets (Patch 5)

## Location

**Site Architect → Presets** (`/site-architect/block-presets`)

## Data model

- Table: `block_presets`
- Service: `App\Services\Deployment\BlockPresetRepository`
- Settings shape: `settings_json` with `style_variant`, `media`, `section`

## Operations

| Action | Method |
|--------|--------|
| Create | Livewire `createPreset` |
| Apply to block | `applyToBlock` → merges into `blocks.settings_json` |
| Clone | `clone` |
| Export / Import | JSON via UI |
| Delete | Non-built-in only |
| Preview | Renders target block with preset merged |

## Permissions

`deployment_engine.block_preset_roles` (editor, manager, admin, super_admin)

## Compatibility

Presets never replace block `code` — only `settings_json`. Pages remain editable in Site Architect → Pages.
