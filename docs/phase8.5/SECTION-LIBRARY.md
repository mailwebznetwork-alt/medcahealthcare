# Section Library (Patch 9)

## Location

**Site Architect → Sections** (`/site-architect/section-library`)

## Concept

A section = ordered list of blocks with optional `style_variant`, `media`, `section` per block in `blocks_json`.

## Operations

| Action | Behaviour |
|--------|-----------|
| Create | Comma-separated block slugs |
| Capture from page | Reads page content tokens + overrides |
| Insert into page | Appends block tokens + merges overrides |
| Clone / Delete | Repository methods |
| Export / Import | JSON |
| Preview | Expands to `{{block:slug}}` and parses via `ContentParser` |

## Page token

`{{section:slug}}` expands at render time; inserted sections use standard block tokens for editability.
