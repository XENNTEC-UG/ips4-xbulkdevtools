# X Bulk Dev Tools Features

> Compile, build, and download multiple IPS applications and plugins in bulk from one ACP page. `xbulkdevtools` streamlines IPS development workflows with bulk JS compilation, XML building, and ZIP bundling.

## Current Version

| Key | Value |
|---|---|
| Version | 1.1.0 |
| Long Version | 10100 |
| Type | Plugin |
| Compatibility | IPS4 4.7+ / PHP 8.1+ (IN_DEV mode required) |

## Feature Pillars

| Pillar | Summary | Benefit |
| --- | --- | --- |
| Bulk JavaScript compilation | Compile JS for multiple selected applications in a single operation | Eliminates repetitive per-app JS compilation during development |
| Bulk application building | Build XML, language files, themes, hooks, and resources for selected apps at once | Full app packaging in one click instead of building each app individually |
| Bulk rebuild and download | Rebuild and download multiple apps as a ZIP bundle or individual .tar files | Rapid distribution-ready packaging for multi-app deployments |
| Bulk plugin download | Export multiple plugins as installable XML files in ZIP or individual mode | Streamlines plugin release preparation across the entire plugin set |
| Smart selection defaults | Custom apps pre-checked, IPS core apps available but unchecked; all plugins pre-checked | Sensible defaults reduce manual selection for the common case |
| Per-app error handling | Failures on individual apps do not block the remaining queue | One broken app never stalls the entire batch operation |
| Progress indicator | Displays current app name, count, and percentage during processing | Clear visibility into long-running bulk operations |
| Download results page | Post-build page offering both ZIP bundle and individual file downloads | Flexible retrieval — grab everything or pick specific outputs |
| Auto-sequential download | Hidden iframes trigger sequential file downloads when individual mode is chosen | Browser-friendly multi-file download without manual clicking |
| Plugin version sync | Detects mismatches between dev/versions.json and the database with comparison table | Prevents version drift between source and runtime environments |
| One-click version fix | "Fix All" button updates database records to match dev source and clears plugin cache | Instant correction of version mismatches without manual DB edits |

## Ideal Use Cases

- IPS developers managing multiple applications needing batch build workflows.
- Teams deploying updates across many apps simultaneously.
- Development environments where individual app builds are too time-consuming.
