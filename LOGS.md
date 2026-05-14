# Logs

Chronological record of decisions, changes, and notes during development of `wpb-mcp-servers-list`.

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
