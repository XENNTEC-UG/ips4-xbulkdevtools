# X Bulk Dev Tools

IPS4 plugin for development installations that adds bulk operations to the ACP Applications and Plugins pages. On the Applications page, it replaces the default "Build All" action with a dialog for compiling JavaScript, building applications, or rebuilding applications before download. On the Plugins page, it adds bulk XML export and a version comparison that can update database version fields from each plugin's `dev/versions.json`. Application TAR files and plugin XML files can be downloaded individually or together in ZIP archives. The added actions are available only when `IN_DEV` is enabled.

## Read Order

1. [GitHub Issues](https://github.com/XENNTEC-UG/ips4-xbulkdevtools/issues): open tasks and bugs
2. [ARCHITECTURE.md](ARCHITECTURE.md): hook design and data flow
3. [FEATURES.MD](FEATURES.MD): capability overview
4. [FLOW.md](FLOW.md): UI flow and processing steps
5. [TEST_RUNTIME.md](TEST_RUNTIME.md): manual verification procedures

## Source Paths

| File | Purpose |
|---|---|
| `plugin-source/hooks/applicationsController.php` | Hook on Applications controller: bulk build/compile/download |
| `plugin-source/hooks/pluginsController.php` | Hook on Plugins controller: bulk plugin download + version sync |
| `plugin-source/dev/lang.php` | Language strings (prefix: `xbdt_`) |
| `plugin-source/dev/hooks.json` | Hook registrations |
| `plugin-source/dev/versions.json` | Version registry |

## Source of Truth

- **Source**: `ips-dev-source/plugins/xbulkdevtools/plugin-source/`
- **Runtime**: `data/ips/plugins/xbulkdevtools/`

## Global Context

- [README.md](../../../../README.md): stack setup
- [IPS4_DEV_GUIDE.md](../../../../IPS4_DEV_GUIDE.md): coding standards
- [AI_TOOLS.md](../../../../AI_TOOLS.md): tool reference
- [CLAUDE.md](../../../../CLAUDE.md): project routing
