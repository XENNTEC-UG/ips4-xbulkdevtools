# X Bulk Dev Tools

Developer productivity plugin for IPS4 ACP that adds bulk developer operations to both the Applications and Plugins pages. Replaces the default "Build All" button on the Applications page with a selection dialog for bulk Compile JS, Build, or Rebuild & Download. Adds a "Bulk Download Plugins" button to the Plugins page for exporting multiple plugins at once. Supports ZIP bundle or individual file downloads for both.

## Read Order

1. [GitHub Issues](https://github.com/XENNTEC-UG/ips4-xbulkdevtools/issues) — open tasks and bugs
2. [ARCHITECTURE.md](ARCHITECTURE.md) — hook design and data flow
3. [FEATURES.MD](FEATURES.MD) — capability overview
4. [FLOW.md](FLOW.md) — UI flow and processing steps
5. [TEST_RUNTIME.md](TEST_RUNTIME.md) — manual verification procedures

## Source Paths

| File | Purpose |
|---|---|
| `plugin-source/hooks/applicationsController.php` | Hook on Applications controller — bulk build/compile/download |
| `plugin-source/hooks/pluginsController.php` | Hook on Plugins controller — bulk plugin download |
| `plugin-source/dev/lang.php` | Language strings |
| `plugin-source/dev/hooks.json` | Hook registrations |

## Source of Truth

- **Source**: `ips-dev-source/plugins/xbulkdevtools/plugin-source/`
- **Runtime**: `data/ips/plugins/xbulkdevtools/`

## Global Context

- [README.md](../../../README.md) — stack setup
- [IPS4_DEV_GUIDE.md](../../../IPS4_DEV_GUIDE.md) — coding standards
- [AI_TOOLS.md](../../../AI_TOOLS.md) — tool reference
- [CLAUDE.md](../../../CLAUDE.md) — project routing
