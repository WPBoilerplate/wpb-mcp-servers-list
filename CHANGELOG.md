# Changelog

All notable changes to `wpboilerplate/wpb-mcp-servers-list` will be documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-05-14

### Added
- `McpServersList` singleton — collects all MCP servers registered by the MCP Adapter plugin.
- `ServerData` value object with full server metadata (id, name, description, version, endpoint URL, route namespace, route).
- `ToolData`, `ResourceData`, `PromptData` value objects for per-server components.
- `RestEndpoint` — optional REST endpoint (`GET /wp-json/wpb-mcp-servers-list/v1/servers`) with configurable namespace, route, and capability.
- `McpServersList::bootstrap()` convenience method for one-line hook registration.
- `McpServersList::is_adapter_available()` to detect the MCP Adapter plugin at runtime.
- JSON Schema definition via `RestEndpoint::get_schema()`.
- All data objects implement `JsonSerializable` for direct use with `wp_send_json` / `WP_REST_Response`.
- PSR-4 autoloading under `WPBoilerplate\McpServersList\`.
- Soft dependency on MCP Adapter — the package degrades gracefully when the adapter is not active.
