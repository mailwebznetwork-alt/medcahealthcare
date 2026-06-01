# L. Admin User Guide — Deployment Engine

## Who can use Blueprint Builder?

Roles: **manager**, **admin**, **super_admin** (with Site Architect module access).

Editors can manage block presets but cannot run blueprint generation.

## Generate a site structure

1. Open **Site Architect → Blueprint Builder**.
2. Select **Industry** and **Blueprint**.
3. Choose **Style pack**, **Theme preset**, and **Layout**.
4. Click **Generate pages (draft)**.
5. Open **Site Architect → Pages** — edit generated pages as normal (standard blocks).
6. Open **Settings → Appearance** to preview/publish theme colors and typography.

## Important

- Generation does **not** replace existing page content silently for active pages — it updates pages by slug.
- Theme/style changes on draft require **Publish** in Appearance for public visitors.
- Blocks are **not duplicated** — blueprints reference existing managed block slugs (`hero-home`, etc.).
