# X Bulk Dev Tools Features

`xbulkdevtools` adds batch build and download actions to the ACP Applications and Plugins pages when IPS is running in development mode.

## Current Version

| Key | Value |
|---|---|
| Version | 1.1.0 |
| Long Version | 10100 |
| Type | Plugin |

## Capabilities

| Capability | Code behavior |
| --- | --- | --- |
| Bulk JavaScript compilation | Creates the selected application's JavaScript XML when its data directory is writable, compiles its JavaScript, and compiles `global` when the selected application is `core` |
| Bulk application build | Calls `build()` for each selected application through a `MultipleRedirect` queue |
| Application rebuild and download | Builds each selected application, then offers individual TAR downloads and a combined ZIP download |
| Bulk plugin export | Builds installable XML for each selected plugin and offers individual XML downloads and a combined ZIP download |
| Selection defaults | Lists custom applications first and preselects them, leaves IPS applications unselected, and preselects all plugins |
| Batch progress and errors | Reports item name, position, and percentage, records per-item exceptions, and continues with later items |
| Individual download mode | Starts sequential downloads from the results page with hidden iframes and a 1.5 second interval |
| Plugin version comparison | Reads the highest long-version key from each plugin's `dev/versions.json` and compares both version fields with the plugin database record |
| Plugin version update | Updates mismatched `core_plugins` version fields from `dev/versions.json`, skips plugins without usable version data, and clears the plugin cache entry |

## Runtime Boundary

All added ACP actions require `\IPS\IN_DEV`. Processing and download endpoints use CSRF checks. The plugin has no settings, scheduled tasks, or database tables of its own.
