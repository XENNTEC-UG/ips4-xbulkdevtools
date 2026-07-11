# Architecture: X Bulk Dev Tools

## Overview

This plugin registers two controller hooks for bulk developer operations in the ACP. It has no settings file, settings metadata, task metadata, task classes, widgets, templates, or database tables. Runtime state for batch processing and download results is stored in `$_SESSION`, while generated archives and plugin XML use `\IPS\TEMP_DIRECTORY`.

## Hooks

### Hook 1: Applications Controller

**Target**: `\IPS\core\modules\admin\applications\applications`

| Method | Purpose |
|---|---|
| `manage()` | Calls the parent method, then replaces the `build_all` sidebar action when `IN_DEV` is enabled |
| `xbdtBulkTools()` | Dialog form with action + download mode + app selection |
| `xbdtProcess()` | `MultipleRedirect` processor that iterates selected applications |
| `xbdtDownloadResults()` | Results page with ZIP and individual .tar download links |
| `xbdtDownloadTar()` | Sends single app .tar to browser |
| `xbdtDownloadZip()` | Bundles all selected apps into one .zip |

### Hook 2: Plugins Controller

**Target**: `\IPS\core\modules\admin\applications\plugins`

| Method | Purpose |
|---|---|
| `manage()` | Calls the parent method, then adds Bulk Download Plugins and Sync Plugin Versions sidebar actions when `IN_DEV` is enabled |
| `xbdtBulkPlugins()` | Dialog form with download mode + plugin selection |
| `xbdtPluginProcess()` | `MultipleRedirect` processor that builds XML for each plugin |
| `xbdtPluginDownloadResults()` | Results page with ZIP and individual .xml download links |
| `xbdtPluginDownloadXml()` | Sends single plugin .xml to browser |
| `xbdtPluginDownloadZip()` | Bundles all selected plugins into one .zip |
| `xbdtSyncPluginVersions()` | Comparison page showing DB vs dev/versions.json for each plugin |
| `xbdtSyncPluginVersionsFix()` | Updates DB records to match dev/versions.json, clears plugin cache |
| `xbdtBuildPluginXml()` | Builds plugin XML (mirrors core download without DB side-effects) |

### App Actions

| Action | What It Does Per App |
|---|---|
| `compilejs` | Creates `data/javascript.xml` when the data directory is writable, compiles the selected application, and also compiles `global` for the `core` application |
| `build` | Calls `$application->build()` |
| `download` | `$application->build()` then offers .tar/.zip downloads |

### Plugin Actions

| Action | What It Does Per Plugin |
|---|---|
| `download` | Builds installable XML (hooks, settings, tasks, widgets, HTML, CSS, JS, resources, lang, versions) |

## App Filtering

- `\IPS\IPS::$ipsApps` array distinguishes IPS vs custom apps
- Custom apps listed first and pre-checked
- IPS apps listed second and unchecked

## Data Flow

1. Dialog form → stores selections in `$_SESSION`
2. `xbdtProcess()` / `xbdtPluginProcess()` reads session, initializes MultipleRedirect `$data` array
3. Each step processes one app/plugin, increments index, reports progress
4. Completion: Build/CompileJS redirect to apps page; Download redirects to results page
5. Results page reads session for processed items, renders download links
6. TAR endpoints build application archives on request. Plugin XML is read from its temporary file or rebuilt if that file no longer exists. ZIP endpoints assemble the selected outputs and send them to the browser.

## Hook Class IDs

| Hook File | Class Name |
|---|---|
| `applicationsController.php` | `hook474` |
| `pluginsController.php` | `hook475` |

## Error Codes

| Code | Location | Meaning |
|---|---|---|
| `2XBDT/1` | `xbdtBulkTools()` | `IN_DEV` is not enabled |
| `2XBDT/2` | `xbdtProcess()` | `IN_DEV` is not enabled |
| `2XBDT/3` | `xbdtDownloadResults()` | `IN_DEV` is not enabled |
| `2XBDT/4` | `xbdtDownloadTar()` | `IN_DEV` is not enabled |
| `2XBDT/5` | `xbdtDownloadZip()` | `IN_DEV` is not enabled |
| `2XBDT/6` | `xbdtDownloadZip()` | No apps selected in session |
| `2XBDT/7` | `xbdtDownloadZip()` | Could not create ZIP archive |
| `2XBDT/P1` | `xbdtBulkPlugins()` | `IN_DEV` is not enabled |
| `2XBDT/P2` | `xbdtPluginProcess()` | `IN_DEV` is not enabled |
| `2XBDT/P3` | `xbdtPluginDownloadResults()` | `IN_DEV` is not enabled |
| `2XBDT/P4` | `xbdtPluginDownloadXml()` | `IN_DEV` is not enabled |
| `2XBDT/P5` | `xbdtPluginDownloadZip()` | `IN_DEV` is not enabled |
| `2XBDT/P6` | `xbdtPluginDownloadZip()` | No plugins selected in session |
| `2XBDT/P7` | `xbdtPluginDownloadZip()` | Could not create ZIP archive |
| `2XBDT/P8` | `xbdtSyncPluginVersions()` | `IN_DEV` is not enabled |
| `2XBDT/P9` | `xbdtSyncPluginVersionsFix()` | `IN_DEV` is not enabled |

## Settings and Tasks

The plugin defines no settings or scheduled tasks. `xbdtBuildPluginXml()` can include settings and tasks from other selected plugins when their source files exist, but those branches are part of the export implementation rather than settings or tasks owned by X Bulk Dev Tools.

## Plugin XML Builder (`xbdtBuildPluginXml`)

The `xbdtBuildPluginXml()` helper creates plugin XML from plugin metadata, database hook records, hook files, and available files in the selected plugin's source directory. Sections included:

| Section | Source |
|---|---|
| Hooks | `core_hooks` DB rows + `hooks/*.php` files |
| Settings | `dev/settings.json` |
| Uninstall code | `uninstall.php` |
| Settings code | `settings.php` |
| Tasks | `dev/tasks.json` + `tasks/*.php` files |
| Widgets | `dev/widgets.json` + `widgets/*.php` files (with location/ID placeholders) |
| HTML/CSS/JS/Resources | `dev/{html,css,js,resources}/` directories (base64-encoded) |
| Language strings | `dev/lang.php` + `dev/jslang.php` |
| Versions | `dev/versions.json` + `dev/setup/*.php` install/upgrade steps |

The built XML includes plugin metadata attributes: `name`, `version_long`, `version_human`, `author`, `website`, `update_check`.

## Code Patterns

- Hook action methods use `try { ... } catch ( \Error | \RuntimeException $e )` with parent fallback. Per-item batch work catches `\Exception` so later items can continue.
- ACP actions check `\IPS\IN_DEV` before performing plugin behavior.
- Processing, individual download, ZIP download, and version-fix endpoints call `\IPS\Session::i()->csrfCheck()`.
- Form validators throw `\DomainException`
- Language prefix: `xbdt_`
- Temp files use `\IPS\TEMP_DIRECTORY` and are cleaned up after download
- Errors are collected (not thrown) during MultipleRedirect so processing continues for remaining items

## Persistence

The plugin has no tables. Session storage carries selections, errors, and generated-item metadata between requests. The version-fix action directly updates `plugin_version_long` and `plugin_version_human` in `core_plugins`, then unsets the IPS plugin cache entry.
