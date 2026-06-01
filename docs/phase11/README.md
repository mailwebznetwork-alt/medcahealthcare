# Phase 11 — Marketing Automation, Attribution & Analytics Platform

**Status:** Complete  
**Tests:** Run `php artisan test` after migrate

---

## Architecture Summary

Phase 11 adds a modular marketing intelligence layer **without modifying existing lead form UX or business logic**.

```
Public visit → CaptureMarketingAttributionMiddleware → session + first-touch cookie
Public clicks → medcaTrack() → POST /marketing/track → marketing_click_events
Lead API → LeadAttributionService → leads (attribution columns) → LeadObserver → pipeline + conversions
Admin → /marketing/intelligence → Livewire IntelligenceDashboard
Scheduler → AggregateMarketingAnalyticsJob + PurgeMarketingAnalyticsJob
```

**Feature flags:** `config/marketing_automation.php` (`MARKETING_AUTOMATION_ENABLED`, per-module toggles)

**Isolation:** Admin theme unchanged. Public tracking is async beacon/fetch (non-blocking).

---

## Migrations

| Migration | Purpose |
|-----------|---------|
| `2026_05_31_100000_add_marketing_attribution_columns_to_leads_table` | UTM, gclid, first/last touch, geo, device, pipeline |
| `2026_05_31_100001_create_marketing_click_events_table` | CTA, WhatsApp, phone, form events |
| `2026_05_31_100002_create_lead_pipeline_tables` | Stage history, activities, conversion events |
| `2026_05_31_100003_create_marketing_analytics_daily_stats` | Aggregated daily metrics |

---

## Models

- `Lead` (extended)
- `MarketingClickEvent`
- `LeadPipelineStageHistory`
- `LeadActivity`
- `MarketingConversionEvent`
- `MarketingAnalyticsDailyStat`

## Enums

- `LeadPipelineStage` (CRM stages, maps to legacy `LeadStatus`)
- `LeadSource` (extended: direct, referral, email, linkedin)

---

## Services

| Service | Module |
|---------|--------|
| `UtmCaptureService` | UTM engine |
| `AttributionSessionStore` | Session + cookie first-touch |
| `LeadAttributionService` | Lead enrichment |
| `DeviceContextResolver` | Device/browser/OS/geo |
| `MarketingClickTrackingService` | Click events |
| `MarketingTrackingValidator` | Security validation |
| `LeadPipelineService` | CRM pipeline |
| `MarketingAnalyticsAggregator` | Executive + WhatsApp/call metrics |
| `MarketingConversionMetricsService` | Funnel metrics |
| `MarketingAttributionReportService` | First/last touch + GBP |
| `MarketingReportExporter` | CSV export |
| `MarketingDataRetentionService` | Retention cleanup |

---

## Middleware

- `CaptureMarketingAttributionMiddleware` (web stack, public pages)

---

## Routes

| Route | Purpose |
|-------|---------|
| `GET /marketing/intelligence` | Executive dashboard |
| `POST /marketing/track` | Public click ingest (rate limited) |
| `GET /marketing/reports/leads/export` | CSV export |

---

## Permissions

- Intelligence dashboard: `module:marketing` + manager/admin/super_admin
- CSV export: same as marketing module
- Lead pipeline: existing `LeadPolicy` (operations module)

---

## Deployment

```bash
php artisan migrate --force
php artisan test
```

Ensure queue worker runs for `AggregateMarketingAnalyticsJob` if using queues.

Env (optional):

```
MARKETING_AUTOMATION_ENABLED=true
MARKETING_ATTRIBUTION_ENABLED=true
MARKETING_CLICK_TRACKING_ENABLED=true
MARKETING_RETENTION_CLICK_EVENTS_DAYS=365
```

---

## Rollback

```bash
php artisan migrate:rollback --step=4
```

Set `MARKETING_AUTOMATION_ENABLED=false` for instant disable without rollback.

Existing leads retain core fields; attribution columns nullable.

---

## Security Review

- UTM/click payload sanitization (strip tags, max lengths)
- Event type whitelist
- Click dedupe cache (3s default)
- Rate limit `marketing_clicks` per IP
- Lead API unchanged auth (X-API-KEY)
- CSRF exempt only for `marketing/track` (beacon support)

---

## Performance Review

- Click tracking: sendBeacon + async fetch, no render blocking
- Executive metrics cached 900s
- Daily aggregation job (off-peak)
- Indexed columns on leads + click events
- No runtime Tailwind/CSS changes

---

## Dashboard Screens (description)

**`/marketing/intelligence`**

- **Executive:** total/qualified/converted leads, conversion rate, top sources/campaigns, trend chart
- **WhatsApp:** today/week/month clicks, source breakdown, top pages
- **Calls:** click-to-call metrics, mobile vs desktop
- **Attribution:** first-touch vs last-touch comparison, GBP widgets
- **Conversions:** funnel counts, velocity, time-to-conversion
- **Reporting:** CSV export with date filters

---

## Future Enhancements

- Excel export (Maatwebsite/Excel)
- Real-time dashboard websockets
- CRM pipeline UI in Bookings show (stage picker)
- GeoIP database integration
- Google Ads offline conversion upload
