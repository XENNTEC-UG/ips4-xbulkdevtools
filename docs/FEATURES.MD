# Features — X Bulk Dev Tools

## Current Version

| Key | Value |
|---|---|
| Version | 1.1.0 |
| Long Version | 10100 |
| Type | Plugin |
| Compatibility | IPS4 4.7+ / PHP 8.1+ (IN_DEV mode required) |

## Capabilities

### Applications Page

- Bulk Compile JavaScript for selected apps
- Bulk Build Application (XML, lang, themes, hooks) for selected apps
- Bulk Rebuild & Download with choice of ZIP bundle or individual .tar files
- App selection with custom apps pre-checked, IPS apps available but unchecked
- Per-app error handling — failures don't block remaining apps
- Progress indicator during processing (app name + count + percentage)
- Download results page with both ZIP and individual download options
- Auto-sequential download via hidden iframes when individual mode is chosen

### Plugins Page

- Bulk Download Plugins as installable XML files
- Plugin selection with all plugins pre-checked
- ZIP bundle or individual .xml file download modes
- Same progress indicator and error handling as applications
- Plugin XML includes hooks, settings, tasks, widgets, HTML, CSS, JS, resources, lang, versions

### Sync Plugin Versions

- Detects version mismatches between dev/versions.json and the database
- Comparison table showing DB version vs dev version for each plugin
- One-click "Fix All" to update database records to match dev source
- Clears plugin cache after fix so IPS picks up changes immediately
