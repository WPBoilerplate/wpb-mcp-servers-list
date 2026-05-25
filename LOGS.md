# Logs

Chronological record of decisions, changes, and notes during development of `wpb-mcp-servers-list`.

---

## 2026-05-25

### Version reset to 0.0.1

- Reset versioning to 0.0.1 for initial WordPress.org plugin directory submission.
- Upgraded `automattic/jetpack-autoloader` from `v5.0.17` to `v5.0.18` to resolve WordPress.org outdated-library flag.
- Consolidated all previous release history (internal 1.0.0–1.0.2) into this single public release.

---

## 2026-05-14 (patch)

### Permission filter

- Added `wpb_mcp_servers_list_rest_capability` filter to `RestEndpoint` permission callback so consuming plugins can change the required capability at runtime without re-registering the endpoint.
- Default remains `manage_options`; the filter is evaluated on every request, ensuring the restriction is always enforced server-side.

---

## 2026-05-14

### Initial implementation

- Designed package as a display-free Composer library; all styling and UI deferred to consuming plugins.
- Chose soft dependency on MCP Adapter (class_exists guard) to avoid Packagist coupling with a WordPress plugin.
- Implemented `McpServersList` singleton with explicit `collect()` lifecycle: consumers hook at `rest_api_init` priority 20+, after MCP Adapter initialises at priority 15.
- Added `ServerData`, `ToolData`, `ResourceData`, `PromptData` value objects implementing `JsonSerializable`.
- Added `RestEndpoint` as fully opt-in: no routes are registered unless the consuming plugin explicitly calls `RestEndpoint::register()`.
- PSR-4 autoloading configured under `WPBoilerplate\McpServersList\`.
- Initialised git repository with remote `git@github.com:WPBoilerplate/wpb-mcp-servers-list.git`.
