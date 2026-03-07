# X Bulk Dev Tools — Bulk Developer Operations for IPS4

An IPS4 plugin that adds bulk developer operations to the AdminCP. Compile JavaScript, build applications, and download release packages for multiple apps and plugins at once — instead of one at a time through the default IPS4 developer center.

---

## Why?

The default IPS4 developer center only lets you compile, build, or download one application at a time. If you maintain multiple apps and plugins, this means clicking through the same workflow repeatedly. X Bulk Dev Tools replaces the "Build All" button on the Applications page with a selection dialog that supports bulk operations, and adds a "Bulk Download Plugins" button to the Plugins page.

## Features

### Applications Page
- **Bulk Compile JavaScript** — Compile JS for all selected apps in one action
- **Bulk Build** — Build XML, language files, themes, and hooks for selected apps
- **Bulk Rebuild & Download** — Full rebuild with choice of ZIP bundle or individual .tar files
- Custom app selection dialog with your apps pre-checked and IPS core apps available but unchecked
- Per-app error handling — failures don't block remaining apps
- Progress indicator during processing (app name, count, percentage)
- Download results page with both ZIP and individual download options

### Plugins Page
- **Bulk Download Plugins** — Export multiple plugins as installable XML files in one action
- Plugin selection with all plugins pre-checked
- ZIP bundle or individual .xml file download modes
- Same progress indicator and error handling as applications

## Requirements

| Requirement | Version |
|---|---|
| IPS4 / Invision Community | 4.7+ |
| PHP | 8.0+ |
| Developer Mode | `IN_DEV` must be enabled |

## Installation

### 1. Download

Download the latest release from the [Releases](https://github.com/XENNTEC-UG/ips4-xbulkdevtools/releases) page.

### 2. Install via ACP

1. Go to **AdminCP > System > Plugins**
2. Click **Install** and upload the plugin XML file
3. The bulk operations will appear automatically on the Applications and Plugins pages

## File Structure

```
plugin-source/
  hooks/
    applicationsController.php   Hook on Applications controller — bulk build/compile/download
    pluginsController.php        Hook on Plugins controller — bulk plugin download
  dev/
    lang.php                     Language strings
    hooks.json                   Hook registrations
    versions.json                Version history
```

## Documentation

| Document | Description |
|---|---|
| [FEATURES.MD](docs/FEATURES.MD) | Capability overview and current version |
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | Hook design and data flow |
| [FLOW.md](docs/FLOW.md) | UI flow and processing steps |
| [TEST_RUNTIME.md](docs/TEST_RUNTIME.md) | Manual verification procedures |
| [Releases](https://github.com/XENNTEC-UG/ips4-xbulkdevtools/releases) | Version history and release notes |

## Contributing

Contributions are welcome. Please open an issue first to discuss what you'd like to change.

## License

This project is free to use. See the repository for license details.

## Links

- [IPS4 / Invision Community](https://invisioncommunity.com) — Community platform
- [XENNTEC](https://xenntec.com) — Developer

---

**Made by [XENNTEC](https://github.com/XENNTEC-UG)**
