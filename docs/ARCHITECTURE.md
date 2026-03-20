# Architecture — X Bulk Dev Tools

## Overview

Two-hook plugin that extends the ACP Applications and Plugins controllers with bulk developer operations.

## Hooks

### Hook 1: Applications Controller

**Target**: `\IPS\core\modules\admin\applications\applications`

| Method | Purpose |
|---|---|
| `manage()` | Override — replaces Build All sidebar button with Bulk Dev Tools |
| `xbdtBulkTools()` | Dialog form with action + download mode + app selection |
| `xbdtProcess()` | MultipleRedirect processor — iterates selected apps |
| `xbdtDownloadResults()` | Results page with ZIP and individual .tar download links |
| `xbdtDownloadTar()` | Sends single app .tar to browser |
| `xbdtDownloadZip()` | Bundles all selected apps into one .zip |

### Hook 2: Plugins Controller

**Target**: `\IPS\core\modules\admin\applications\plugins`

| Method | Purpose |
|---|---|
| `manage()` | Override — adds Bulk Download Plugins and Sync Plugin Versions sidebar buttons |
| `xbdtBulkPlugins()` | Dialog form with download mode + plugin selection |
| `xbdtPluginProcess()` | MultipleRedirect — builds XML for each plugin |
| `xbdtPluginDownloadResults()` | Results page with ZIP and individual .xml download links |
| `xbdtPluginDownloadXml()` | Sends single plugin .xml to browser |
| `xbdtPluginDownloadZip()` | Bundles all selected plugins into one .zip |
| `xbdtSyncPluginVersions()` | Comparison page showing DB vs dev/versions.json for each plugin |
| `xbdtSyncPluginVersionsFix()` | Updates DB records to match dev/versions.json, clears plugin cache |
| `xbdtBuildPluginXml()` | Builds plugin XML (mirrors core download without DB side-effects) |

### App Actions

| Action | What It Does Per App |
|---|---|
| `compilejs` | `Javascript::createXml()` + `Javascript::compile()` — JS only |
| `build` | `$application->build()` — full XML/lang/theme/JS/hooks rebuild |
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
6. ZIP/Tar/XML endpoints create archives and send output

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

## Plugin XML Builder (`xbdtBuildPluginXml`)

The `xbdtBuildPluginXml()` helper method mirrors the IPS4 core plugin download logic without triggering DB side-effects. It builds a complete installable XML by reading from the plugin's dev directory. Sections included:

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

- All methods use `try { ... } catch ( \Error | \RuntimeException $e )` with parent fallback
- Every action method checks `\IPS\IN_DEV` and returns error `2XBDT/*` if not enabled
- CSRF check on processing and download endpoints via `\IPS\Session::i()->csrfCheck()`
- Form validators throw `\DomainException`
- Language prefix: `xbdt_`
- Temp files use `\IPS\TEMP_DIRECTORY` and are cleaned up after download
- Errors are collected (not thrown) during MultipleRedirect so processing continues for remaining items

## No Database

Plugin has no tables. Session storage is used for inter-request state during MultipleRedirect processing.
