# Phase 8 — Theme Management System & Design System

**Medca Healthcare / MarkOnMinds**  
**Status:** Complete  
**Date:** 2026-05-30  
**Tests:** 289/289 passing (18 Phase 8 tests)

---

## A. Theme Architecture Report

### Current state (pre-Phase 8 foundation)

| Layer | Location | Namespace |
|-------|----------|-----------|
| Public tokens | `resources/css/public/tokens.css` | `--medca-*` |
| Admin tokens | `resources/css/markonminds.css` | `--mom-*` (UI-locked) |
| Token registry | `config/theme.php` | Dual-shell map |
| Component registry | `config/components.php` | `x-public.*`, `x-admin.*` |
| Vite entries | `public.css`, `admin.css` | Isolated bundles |

### Phase 8 architecture

```
┌─────────────────────────────────────────────────────────────┐
│  Settings → Appearance (Livewire)                           │
│  app/Livewire/Settings/AppearanceSettings.php               │
└──────────────────────────┬──────────────────────────────────┘
                           │ draft save / publish
                           ▼
┌─────────────────────────────────────────────────────────────┐
│  ThemeConfigRepository                                      │
│  - draft_public, draft_branding, draft_typography           │
│  - published_public, branding, typography (live)            │
└──────────────────────────┬──────────────────────────────────┘
                           │
         ┌─────────────────┼─────────────────┐
         ▼                 ▼                 ▼
  ThemePresetRegistry  ThemeContrastValidator  ThemeCssVariableBuilder
         │                 │                 │
         ▼                 └────────┬────────┘
  theme_presets (DB)               ▼
                           ThemeResolver
                           - publicTokens()
                           - branding()
                           - headerPresetClass()
                           - layoutMainClasses()
                           - previewModeActive() (session)
                                    │
                                    ▼
                    <x-theme.public-vars /> → layouts/app.blade.php
                    global/header.blade.php (branding + header class)
```

**Isolation:** Admin shell (`markonminds.css`, `--mom-*`) is never modified by Appearance settings. Only the public marketing surface receives runtime CSS variable overrides.

### Risks & rollback

| Risk | Mitigation |
|------|------------|
| Low-contrast colors | `ThemeContrastValidator` blocks save/publish |
| Draft goes live early | Separate draft columns; publish is super_admin only |
| Admin theme bleed | No admin token injection; tests assert isolation |
| Bad preset import | JSON validation + contrast check |

**Rollback:** `php artisan migrate:rollback --step=1` removes theme tables; public site falls back to static `tokens.css` defaults. Published assets in `storage/app/public/theme-assets` remain but are unused.

---

## B. Appearance Module Documentation

**Route:** `GET /settings/appearance` (`settings.appearance`)  
**Middleware:** `auth`, `module:settings`, `role:admin,super_admin`  
**Publish:** super_admin only (Livewire `publish()` + policy)

### Tabs

| Tab | Actions |
|-----|---------|
| Branding | Logo, favicon, company name, tagline, WhatsApp, CTA, email, URL |
| Colors | All `--medca-*` color tokens with live swatch |
| Typography | Heading/body font (whitelist), line-height, letter-spacing |
| Buttons | Live preview of `btn-premium`, `medca-cta-solid` |
| Cards | Preview `x-public.card` + `service-card` |
| Header | 5 header presets (classic, corporate, premium, minimal, modern) |
| Layout | Contained / Wide / Full width |
| Presets | Apply, clone, export, import |
| Preview | Summary + open public site + session preview toggle |

### Preview workflow

1. Edit any tab → **Save draft**
2. **Enable preview** (session flag `theme_preview_public`)
3. Open public site — draft tokens/fonts/layout apply for your browser only
4. **Publish** (super_admin) — copies draft → published, clears draft columns

---

## C. Theme Preset Documentation

**Config:** `config/theme_presets.php`  
**Seeder:** `Database\Seeders\ThemePresetSeeder`

| Slug | Name | Header | Layout |
|------|------|--------|--------|
| `clinical_blue` | Clinical Blue | Classic Healthcare | Contained |
| `premium_gold` | Premium Gold | Premium | Contained |
| `forest_green` | Forest Green | Corporate | Wide |
| `luxury_black` | Luxury Black | Premium | Contained |
| `modern_purple` | Modern Purple | Modern | Wide |

**Operations:** Apply to draft, Clone, Export JSON, Import JSON (creates non-builtin preset).

---

## D. Database Schema Documentation

### `theme_presets`

| Column | Type | Notes |
|--------|------|-------|
| slug | string unique | URL-safe identifier |
| name | string | Display name |
| shell | string | `public` (admin presets reserved) |
| is_builtin | boolean | Seeded from config |
| tokens | json | Color token map |
| branding | json nullable | Optional preset branding |
| header_preset | string nullable | |
| layout_preset | string nullable | |
| typography | json nullable | |

### `theme_configurations` (singleton row)

| Column | Type | Notes |
|--------|------|-------|
| published_public | json nullable | Live color overrides |
| draft_public | json nullable | Draft colors |
| published_admin | json nullable | Reserved (admin UI-locked) |
| draft_admin | json nullable | Reserved |
| branding | json nullable | Published branding |
| draft_branding | json nullable | Draft branding |
| typography | json nullable | Published typography |
| draft_typography | json nullable | Draft typography |
| header_preset | string | Live header preset |
| draft_header_preset | string nullable | |
| layout_preset | string | Live layout |
| draft_layout_preset | string nullable | |
| active_preset_slug | string nullable | Last applied preset |
| updated_by_id / published_by_id | FK users | Audit |
| draft_updated_at / published_at | timestamps | |

**Migration:** `2026_05_30_220000_create_theme_management_tables.php`

---

## E. Theme API Documentation

### Services (internal PHP API)

```php
// Resolve live or preview tokens
app(ThemeResolver::class)->publicTokens();
app(ThemeResolver::class)->publicCssBlock();
app(ThemeResolver::class)->branding();
app(ThemeResolver::class)->brandingValue('brand_name');
app(ThemeResolver::class)->headerPresetClass();
app(ThemeResolver::class)->layoutMainClasses();

// Repository
app(ThemeConfigRepository::class)->saveDraftPublicTokens($tokens, $user);
app(ThemeConfigRepository::class)->publishDraft($user);
app(ThemeConfigRepository::class)->applyPresetToDraft($slug, $user);
app(ThemeConfigRepository::class)->exportPreset($slug);
app(ThemeConfigRepository::class)->importPreset($payload, $user);
```

### HTTP routes

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/settings/appearance` | Appearance UI |
| POST | `/settings/appearance/preview/enable` | Session preview on |
| POST | `/settings/appearance/preview/disable` | Session preview off |

### Blade

```blade
<x-theme.public-vars />
```

Injects `<style id="medca-theme-vars">` and typography overrides on public layout only.

---

## F. Security Review

| Control | Implementation |
|---------|------------------|
| Settings access | `module:settings` + admin/super_admin |
| Publish | super_admin only (403 for admin role) |
| Color validation | Hex regex + WCAG contrast on text/surface |
| Font validation | Whitelist in `config/theme_management.php` |
| Upload validation | MIME whitelist, 2MB logo / 512KB favicon, `public` disk |
| Import validation | JSON schema + contrast on tokens |
| Preview | Session-scoped; requires authenticated admin |

---

## G. Performance Review

| Check | Result |
|-------|--------|
| CSS duplication | Single inline `<style>` block (~20 vars max); no duplicate bundles |
| Bundle growth | No new Vite entries; header preset CSS in existing `components.css` |
| Runtime Tailwind | Not used — CSS variables only |
| Caching | Published tokens cached 3600s (`theme.configuration.published.tokens`) |
| Vite split | `public.css` / `admin.css` unchanged |

**Recommendation:** Rebuild production assets after deploy:  
`node node_modules/tailwindcss/lib/cli.js -i resources/css/public/public.css -o public/build/assets/public.css --minify`

---

## H. User Guide (Public site)

Visitors see the **published** theme automatically. No action required. Branding (logo, name) and colors update when administrators publish changes from Settings → Appearance.

---

## I. Admin Guide

1. Go to **Settings → Appearance**
2. Choose a tab (e.g. **Colors** or **Presets**)
3. Make changes and click **Save draft**
4. Click **Enable preview** and open the public site in a new tab
5. When satisfied, **Publish** (super_admin account required)
6. Use **Reset draft** to discard unpublished work

---

## J. Future Enhancement Roadmap

- Google Fonts API picker with live loading preview
- Admin shell appearance (separate `published_admin` when product unlocks `--mom-*`)
- Button/card variant editor (radius, shadow tokens)
- Theme revision history with one-click rollback
- CDN cache purge hook on publish
- A/B theme experiments for landing pages

---

## Step 1 — Theme Foundation Audit (Summary)

### Token map (`--medca-*`)

Primary, primary-hover, navy family, text (primary/secondary/muted), surfaces, borders, success/warning/danger, shadows, radii — defined in `tokens.css`, registry in `config/theme.php`.

### Component map

`config/components.php` — `x-public.card`, `x-public.hero`, `x-admin.card`, etc.

### Limitations (addressed in Phase 8)

- Tokens were static CSS only → now DB-backed with runtime override
- Branding hardcoded in `config/medca.php` → overridable via Appearance
- No preview/publish workflow → draft/publish + session preview
- Header/layout fixed in Blade → preset-driven classes

---

## Files Modified / Created

### Created
- `app/Models/ThemePreset.php`, `ThemeConfiguration.php`
- `app/Services/Theme/*` (5 services)
- `app/Policies/ThemeConfigurationPolicy.php`
- `app/Livewire/Settings/AppearanceSettings.php`
- `app/Http/Controllers/ThemePreviewController.php`
- `database/migrations/2026_05_30_220000_create_theme_management_tables.php`
- `database/seeders/ThemePresetSeeder.php`
- `config/theme_presets.php`, `config/theme_management.php`
- `resources/views/components/theme/public-vars.blade.php`
- `resources/views/settings/appearance.blade.php`
- `resources/views/livewire/settings/appearance-settings.blade.php`
- `tests/Feature/ThemeManagementTest.php`
- `tests/Feature/AppearanceSettingsTest.php`
- `tests/Feature/ThemePresetTest.php`
- `tests/Feature/ThemePreviewTest.php`
- `tests/Feature/ThemeSecurityTest.php`
- `docs/theme/PHASE8-DELIVERABLES.md`

### Modified
- `routes/web.php`, `SettingsController.php`
- `resources/views/settings/partials/nav.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/global/header.blade.php`
- `resources/css/public/components.css` (header presets)
- `app/Providers/AppServiceProvider.php`

---

## Architecture Scorecard

| Criterion | Score |
|-----------|-------|
| Public/admin isolation | ✅ Pass |
| No business logic changes | ✅ Pass |
| No block governance changes | ✅ Pass |
| Draft/preview/publish | ✅ Pass |
| Non-technical admin UX | ✅ Pass |
| Test coverage | ✅ 18 tests |
| Performance | ✅ Pass |
| Security | ✅ Pass |
| Scalability | ✅ Presets + import/export |
| Documentation | ✅ Complete |

**Overall: 10/10 — Phase 8 complete**
