# Security Review (Patch 14)

## Authorization matrix

| Surface | Policy / gate |
|---------|----------------|
| Appearance / Header | Admin, super_admin (`AppearanceSettings` mount) |
| Global Content | Admin, super_admin |
| Blueprint Builder | `DeploymentEnginePolicy::generatePages` |
| Block Presets / Block Studio / Sections | `manageBlockPresets` |
| Deployment Packages | `managePackages` (admin, super_admin) |
| Theme publish | `ThemeConfiguration` policy `publish` |

## Import / export

- JSON import validated before persistence (presets, sections, packages)
- Package import blocked when validator returns errors
- Built-in presets and sections cannot be deleted

## Publish controls

Only authorized users may **publish** theme drafts to the live site. Draft preview uses session flag only — no public exposure of unpublished branding without preview mode.

## Recommendations

- Keep `package_roles` restricted to admin roles in production
- Review exported JSON before sharing (may contain business contact data)
- Run package validation before import on client projects
