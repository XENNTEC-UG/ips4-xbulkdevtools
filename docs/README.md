# X Bulk Dev Tools

Developer productivity plugin for IPS4 ACP that enhances the Applications page with bulk developer operations. Replaces the default "Build All" button with a selection dialog allowing you to choose which apps to process and what action to perform (Build, Compile JS, or Download), with support for ZIP bundle or individual .tar downloads.

## Read Order

1. [GitHub Issues](https://github.com/XENNTEC-UG/ips4-xbulkdevtools/issues) — open tasks and bugs
2. [ARCHITECTURE.md](ARCHITECTURE.md) — hook design and data flow
3. [FEATURES.MD](FEATURES.MD) — capability overview
4. [FLOW.md](FLOW.md) — UI flow and processing steps
5. [TEST_RUNTIME.md](TEST_RUNTIME.md) — manual verification procedures

## Source Paths

| File | Purpose |
|---|---|
| `plugin-source/hooks/applicationsController.php` | Main hook — all logic |
| `plugin-source/dev/lang.php` | Language strings |
| `plugin-source/dev/hooks.json` | Hook registration |

## Source of Truth

- **Source**: `ips-dev-source/plugins/xbulkdevtools/plugin-source/`
- **Runtime**: `data/ips/plugins/xbulkdevtools/`

## Global Context

- [README.md](../../../README.md) — stack setup
- [IPS4_DEV_GUIDE.md](../../../IPS4_DEV_GUIDE.md) — coding standards
- [AI_TOOLS.md](../../../AI_TOOLS.md) — tool reference
- [CLAUDE.md](../../../CLAUDE.md) — project routing
