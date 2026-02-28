# Architecture — X Bulk Dev Tools

## Overview

Single-hook plugin that extends the ACP Applications controller (`\IPS\core\modules\admin\applications\_applications`) with bulk developer operations.

## Hook Design

### Target

`\IPS\core\modules\admin\applications\_applications` — the main ACP controller for managing installed applications.

### Methods Added

| Method | Purpose |
|---|---|
| `manage()` | Override — replaces Build All sidebar button URL |
| `xbdtBulkTools()` | Dialog form with action + download mode + app selection |
| `xbdtProcess()` | MultipleRedirect processor — iterates selected apps |
| `xbdtDownloadResults()` | Results page with ZIP and individual download links |
| `xbdtDownloadTar()` | Sends single app .tar to browser |
| `xbdtDownloadZip()` | Bundles all selected apps into one .zip |

### Actions

| Action | What It Does Per App |
|---|---|
| `build` | `$application->build()` — full XML/lang/theme/JS/hooks rebuild |
| `compilejs` | `Javascript::createXml()` + `Javascript::compile()` — JS only |
| `download` | `$application->build()` then offers .tar/.zip downloads |

## Data Flow

1. Dialog form → stores selections in `$_SESSION`
2. `xbdtProcess()` reads session, initializes MultipleRedirect `$data` array
3. Each step processes one app, increments index, reports progress
4. Completion: Build/CompileJS redirect to apps page; Download redirects to results page
5. Results page reads session for app list, renders download links
6. ZIP/Tar endpoints create archives from `BuilderIterator` and send output

## App Filtering

- `$app->protected === false` → custom apps (listed first, pre-checked)
- `$app->protected === true` → IPS core apps (listed second, unchecked)

## No Database

Plugin has no tables. Session storage is used for inter-request state during MultipleRedirect processing.
