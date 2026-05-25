# wpb-mcp-servers-list

> A Composer library that retrieves all MCP servers registered in WordPress via the [MCP Adapter](https://github.com/WordPress/mcp-adapter) plugin.

**This package is intentionally display-free.** It provides typed PHP data objects and an optional REST endpoint. How the data is styled and rendered is entirely up to the consuming plugin.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | >= 7.4 |
| WordPress | >= 6.8 |
| [Jetpack Autoloader](https://github.com/Automattic/jetpack-autoloader) | ^5.0 (≥ v5.0.18, Composer dependency) |
| [MCP Adapter plugin](https://github.com/WordPress/mcp-adapter) | >= 0.5.0 (soft dependency) |

The package works gracefully when MCP Adapter is not active — `get_servers()` simply returns an empty array and `is_adapter_available()` returns `false`.

---

## Installation

```bash
composer require wpboilerplate/wpb-mcp-servers-list
```

Make sure your plugin loads the **Jetpack Autoloader** (installed alongside this package):

```php
require_once __DIR__ . '/vendor/autoload_packages.php';
```

> This package uses [Jetpack Autoloader](https://github.com/Automattic/jetpack-autoloader) instead of the standard Composer autoloader to prevent class-version conflicts when multiple plugins require the same package. Always load `vendor/autoload_packages.php`, **not** `vendor/autoload.php`.

---

## Usage

### 1. Collect server data

MCP Adapter registers servers during `rest_api_init` at priority 15. You must collect **after** that:

```php
use WPBoilerplate\McpServersList\McpServersList;

// Option A – one-liner bootstrap (registers the hook automatically)
add_action( 'plugins_loaded', [ McpServersList::class, 'bootstrap' ] );

// Option B – manual hook (more control)
add_action( 'rest_api_init', function () {
    McpServersList::instance()->collect();
}, 20 );
```

### 2. Read the data

```php
use WPBoilerplate\McpServersList\McpServersList;

$servers = McpServersList::instance()->get_servers();
// $servers → ServerData[]

foreach ( $servers as $server ) {
    echo $server->get_name();         // "WordPress MCP Server"
    echo $server->get_id();           // "default"
    echo $server->get_version();      // "0.5.0"
    echo $server->get_endpoint_url(); // "https://example.com/wp-json/wp/mcp/v1/mcp"

    foreach ( $server->get_tools() as $tool ) {
        echo $tool->get_name();        // "discover_abilities"
        echo $tool->get_description(); // "..."
    }

    foreach ( $server->get_resources() as $resource ) {
        echo $resource->get_name();
        echo $resource->get_uri();
        echo $resource->get_description();
    }

    foreach ( $server->get_prompts() as $prompt ) {
        echo $prompt->get_name();
        echo $prompt->get_description();
    }
}
```

### 3. Optional REST endpoint

Register the built-in endpoint so your admin JS can fetch server data:

```php
use WPBoilerplate\McpServersList\RestEndpoint;

add_action( 'rest_api_init', function () {
    // Default: GET /wp-json/wpb-mcp-servers-list/v1/servers  (requires manage_options)
    RestEndpoint::register();

    // Custom namespace / capability at registration time:
    // RestEndpoint::register( 'read', 'my-plugin/v1', '/mcp-servers' );
}, 20 );
```

The endpoint is **admin-only by default** (`manage_options`). Use the `wpb_mcp_servers_list_rest_capability` filter to change the required capability at runtime:

```php
// Allow editors to access the endpoint:
add_filter( 'wpb_mcp_servers_list_rest_capability', function ( string $cap ): string {
    return 'edit_posts';
} );

// Allow any logged-in user:
add_filter( 'wpb_mcp_servers_list_rest_capability', function ( string $cap ): string {
    return 'read';
} );
```

> **Note:** The filter takes precedence over the `$capability` argument passed to `RestEndpoint::register()`. Non-admin users always receive a `401 Unauthorized` response if neither the argument nor the filter grants them access.

Response shape:

```json
{
    "adapter_available": true,
    "servers": [
        {
            "id": "default",
            "name": "WordPress MCP Server",
            "description": "...",
            "version": "0.5.0",
            "endpoint_url": "https://example.com/wp-json/wp/mcp/v1/mcp",
            "route_namespace": "wp/mcp/v1",
            "route": "/mcp",
            "tools": [
                { "name": "discover_abilities", "description": "..." }
            ],
            "resources": [],
            "prompts": []
        }
    ]
}
```

### 4. Check adapter availability

```php
if ( McpServersList::is_adapter_available() ) {
    // MCP Adapter plugin is active
}
```

---

## API Reference

### `McpServersList`

| Method | Returns | Description |
|---|---|---|
| `::instance()` | `McpServersList` | Get singleton instance |
| `::bootstrap()` | `void` | Register `collect()` on `rest_api_init` at priority 20 |
| `::is_adapter_available()` | `bool` | Check if MCP Adapter is active |
| `->collect()` | `void` | Collect all registered servers (idempotent) |
| `->get_servers()` | `ServerData[]` | All collected servers |
| `->get_server( $id )` | `ServerData\|null` | Single server by ID |
| `->has_servers()` | `bool` | Whether any servers were collected |
| `->is_collected()` | `bool` | Whether `collect()` has run |

### `RestEndpoint`

| Method | Description |
|---|---|
| `::register( $capability, $namespace, $route )` | Register the REST endpoint (admin-only by default) |
| `::get_schema()` | Get the JSON schema for the response |

Constants: `RestEndpoint::NAMESPACE`, `RestEndpoint::ROUTE`

**Filter:** `wpb_mcp_servers_list_rest_capability` — override the required WordPress capability at runtime.

```php
// Default behaviour (administrators only):
// apply_filters( 'wpb_mcp_servers_list_rest_capability', 'manage_options' )
```

### `ServerData`

| Method | Returns |
|---|---|
| `get_id()` | `string` |
| `get_name()` | `string` |
| `get_description()` | `string` |
| `get_version()` | `string` |
| `get_endpoint_url()` | `string` |
| `get_route_namespace()` | `string` |
| `get_route()` | `string` |
| `get_tools()` | `ToolData[]` |
| `get_resources()` | `ResourceData[]` |
| `get_prompts()` | `PromptData[]` |
| `to_array()` | `array` |

### `ToolData` / `PromptData`

`get_name()`, `get_description()`, `to_array()`

### `ResourceData`

`get_name()`, `get_uri()`, `get_description()`, `to_array()`

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).
