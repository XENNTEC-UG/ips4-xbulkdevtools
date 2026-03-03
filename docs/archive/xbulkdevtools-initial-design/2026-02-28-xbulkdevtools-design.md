# X Bulk Dev Tools — Design

**Date:** 2026-02-28
**Type:** Plugin
**Directory:** `xbulkdevtools`
**Repo:** `XENNTEC-UG/ips4-xbulkdevtools`

## Purpose

Developer productivity plugin that enhances the ACP Applications page "Build All" button with a selection dialog. Allows bulk Build, Compile JS, and Rebuild & Download operations on selected apps instead of all-or-nothing.

## Architecture

IPS4 plugin that hooks `\IPS\core\modules\admin\applications\_applications` to replace the "Build All" sidebar button with an enhanced "Bulk Dev Tools" dialog.

### Hook: `applicationsController`

**Target:** `\IPS\core\modules\admin\applications\_applications`

Methods:
- `manage()` — overrides sidebar button URL
- `xbdtBulkTools()` — dialog form with action + app selection
- `xbdtProcess()` — MultipleRedirect processing
- `xbdtDownloadResults()` — results page with download options
- `xbdtDownloadTar()` — single app .tar download
- `xbdtDownloadZip()` — all selected apps as .zip bundle

### UI Flow

1. Click "Bulk Dev Tools" button (replaces "Build All")
2. Dialog: action radio (Build / Compile JS / Rebuild & Download) + download mode (ZIP / Individual, shown for Download only) + app checkboxes (custom pre-checked)
3. Submit → MultipleRedirect processes each app sequentially with progress
4. Completion:
   - Build/CompileJS → redirect to apps page with success message
   - Download → redirect to results page with ZIP button + individual links

### App Filtering

- Custom apps: `$app->protected === false` (listed first, pre-checked)
- Core apps: `$app->protected === true` (listed second, unchecked)

### Conventions

| Property | Value |
|---|---|
| Lang prefix | `xbdt_` |
| Short code | `xbdt` |
| Error codes | `2XBDT/N` |

### Files

```
ips-dev-source/plugins/xbulkdevtools/
├── plugin-source/
│   ├── hooks/applicationsController.php
│   ├── dev/
│   │   ├── hooks.json
│   │   ├── lang.php
│   │   ├── jslang.php
│   │   ├── versions.json
│   │   ├── setup/ (index.html)
│   │   ├── css/ (index.html)
│   │   ├── html/ (index.html)
│   │   ├── js/ (index.html)
│   │   └── resources/ (index.html)
│   └── index.html
├── docs/ (README, ARCHITECTURE, FEATURES, FLOW, TEST_RUNTIME)
└── releases/.gitkeep
```

No database tables. No frontend modules. Pure ACP behavioral hook.
