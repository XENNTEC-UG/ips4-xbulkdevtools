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
| `manage()` | Override — adds Bulk Download Plugins sidebar button |
| `xbdtBulkPlugins()` | Dialog form with download mode + plugin selection |
| `xbdtPluginProcess()` | MultipleRedirect — builds XML for each plugin |
| `xbdtPluginDownloadResults()` | Results page with ZIP and individual .xml download links |
| `xbdtPluginDownloadXml()` | Sends single plugin .xml to browser |
| `xbdtPluginDownloadZip()` | Bundles all selected plugins into one .zip |
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

## No Database

Plugin has no tables. Session storage is used for inter-request state during MultipleRedirect processing.
