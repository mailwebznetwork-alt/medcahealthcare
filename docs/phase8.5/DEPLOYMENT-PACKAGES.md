# Deployment Packages (Patch 10)

## Location

**Site Architect → Packages** (`/site-architect/deployment-packages`)

## Manifest format

`markonminds.deployment-package` v1.0.0

Includes: theme draft, style pack, blueprints, block presets, section library, global content variables, media mapping, theme presets metadata.

## UI

- Select style pack, blueprint slugs, sections, block presets
- Export → JSON + validation report
- Validate / Import with compatibility check
- Clone recent packages

## Permissions

`deployment_engine.package_roles` — admin, super_admin (separate from blueprint generator roles).

## Services

- `DeploymentPackageExporter` / `DeploymentPackageImporter`
- `DeploymentPackageValidator` — errors + warnings
