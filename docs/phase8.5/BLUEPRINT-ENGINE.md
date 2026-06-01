# B. Blueprint Engine

**Config:** `config/blueprints.php`

## Built-in blueprints

| Slug | Industry | Pages generated |
|------|----------|-----------------|
| `home_healthcare` | healthcare | home, services, contact (+ landing) |
| `care_home` | care_home | home, about |
| `construction` | construction | home, services |
| `painting` | painting | home |
| `consultancy` | consultancy | home |
| `education` | education | home |

## Generator

`BlueprintPageGenerator::generate()`:

1. Creates/updates `Page` rows with standard `Page::buildContentFromParts()`.
2. Sets `block_overrides_json` (style_variant, media, section scaffolding).
3. Sets `deployment_meta_json` (blueprint, style_pack, theme_preset).
4. Optionally applies theme preset + header/layout to **draft** (not live until Theme publish).
5. Logs row in `deployment_generations`.

Generated pages default to **inactive** when newly created.
