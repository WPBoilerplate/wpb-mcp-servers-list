# Agenda — wpb-mcp-servers-list

## Purpose

Provide a zero-dependency, display-free Composer library that any WordPress plugin can use to retrieve all MCP servers registered via the [MCP Adapter](https://github.com/WordPress/mcp-adapter) plugin. The package owns **data retrieval only**; rendering, styling, and admin UI are the consuming plugin's responsibility.

---

## Core Logic

### Why a separate package?

Multiple WordPress plugins may need access to the same MCP server list. Rather than each plugin reimplementing discovery logic, this package centralises it. Consuming plugins add `composer require wpboilerplate/wpb-mcp-servers-list` and immediately get typed data objects without any coupling to MCP Adapter internals.

### Hook timing

MCP Adapter registers its servers during `rest_api_init` at **priority 15**. This package must collect server data at **priority 20 or later**. The `bootstrap()` helper handles this automatically; manual hook registration is also supported.

### Soft dependency on MCP Adapter

The package does **not** declare `mcp-adapter` as a Composer dependency because it is a WordPress plugin, not a Composer package. Instead, all adapter access is guarded by `class_exists('\\WP\\MCP\\Core\\McpAdapter')`. When the adapter is absent, `get_servers()` returns `[]` and `is_adapter_available()` returns `false`.

### Data flow

```
rest_api_init (priority 15)
  └─ McpAdapter::init()
       └─ do_action('mcp_adapter_init', $adapter)
            └─ servers registered into McpAdapter::$servers

rest_api_init (priority 20)
  └─ McpServersList::collect()
       └─ McpAdapter::instance()->get_servers()
            └─ foreach server → ServerData::from_mcp_server()
                 ├─ ToolData[]
                 ├─ ResourceData[]
                 └─ PromptData[]
```

---

## Design Decisions

| Decision | Rationale |
|---|---|
| **No WordPress hooks registered by default** | The library is a tool, not an actor. Consuming plugins decide when to hook. |
| **Singleton** | Server data is global and immutable within a request; a singleton avoids repeated collection. |
| **`collect()` is idempotent** | Calling it multiple times is safe — subsequent calls are no-ops. |
| **`JsonSerializable` on data objects** | Allows direct use with `WP_REST_Response`, `wp_send_json`, or `json_encode`. |
| **No CSS / JS / admin page** | Consumers own their own UI stack. The package stays dependency-free on the front-end. |
| **REST endpoint is opt-in** | Not all consumers need an HTTP endpoint. Calling `RestEndpoint::register()` is explicit. |
| **PHP 7.4+** | Matches the minimum requirement of MCP Adapter for maximum compatibility. |

---

## Milestones

| Version | Goal |
|---|---|
| **1.0.0** | Core data retrieval, value objects, optional REST endpoint |
| **1.1.0** | Add filtering hooks (`wpb_mcp_servers_list_servers`) so consumers can modify the data |
| **1.2.0** | WP-CLI command: `wp wpb-mcp-servers list` |
| **2.0.0** | Cache layer (transient) for non-REST contexts (admin pages, WP-Cron) |

---

## Consuming Plugin Checklist

- [ ] `composer require wpboilerplate/wpb-mcp-servers-list`
- [ ] Load `vendor/autoload.php` in the plugin bootstrap
- [ ] Call `McpServersList::bootstrap()` or add manual `rest_api_init` hook at priority 20+
- [ ] Use `McpServersList::instance()->get_servers()` to retrieve `ServerData[]`
- [ ] Optionally call `RestEndpoint::register()` if an HTTP endpoint is needed
- [ ] Own all rendering / styling / admin page code
