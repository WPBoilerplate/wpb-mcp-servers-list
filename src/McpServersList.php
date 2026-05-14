<?php
/**
 * Main McpServersList class.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList;

use WPBoilerplate\McpServersList\Data\ServerData;

/**
 * Retrieves and exposes all MCP servers registered via the MCP Adapter plugin.
 *
 * This class is a singleton. It does not register any WordPress hooks on its
 * own — the consuming plugin is responsible for calling collect() at the right
 * moment (during or after `rest_api_init` at priority 20+, so that McpAdapter
 * has already been initialised at priority 15).
 *
 * Typical usage in a consuming plugin:
 *
 *   add_action( 'rest_api_init', function () {
 *       \WPBoilerplate\McpServersList\McpServersList::instance()->collect();
 *   }, 20 );
 *
 *   // Later, to read the data:
 *   $servers = \WPBoilerplate\McpServersList\McpServersList::instance()->get_servers();
 */
class McpServersList {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Collected server data objects.
	 *
	 * @var ServerData[]
	 */
	private $servers = array();

	/**
	 * Whether collect() has already run.
	 *
	 * @var bool
	 */
	private $collected = false;

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Collect all registered MCP servers from McpAdapter.
	 *
	 * Safe to call multiple times — subsequent calls are no-ops.
	 * Must be called during or after `rest_api_init` at priority 20+ so that
	 * McpAdapter (which hooks at priority 15) has finished registering servers.
	 *
	 * @return void
	 */
	public function collect(): void {
		if ( $this->collected ) {
			return;
		}

		$this->collected = true;

		if ( ! self::is_adapter_available() ) {
			return;
		}

		$adapter = \WP\MCP\Core\McpAdapter::instance();

		foreach ( $adapter->get_servers() as $server ) {
			$this->servers[] = ServerData::from_mcp_server( $server );
		}
	}

	/**
	 * Return all collected MCP server data objects.
	 *
	 * Returns an empty array if collect() has not been called yet or if
	 * the MCP Adapter plugin is not active.
	 *
	 * @return ServerData[]
	 */
	public function get_servers(): array {
		return $this->servers;
	}

	/**
	 * Return a single server by ID, or null if not found.
	 *
	 * @param string $id Server ID.
	 * @return ServerData|null
	 */
	public function get_server( string $id ): ?ServerData {
		foreach ( $this->servers as $server ) {
			if ( $server->get_id() === $id ) {
				return $server;
			}
		}

		return null;
	}

	/**
	 * Whether any servers have been collected.
	 *
	 * @return bool
	 */
	public function has_servers(): bool {
		return ! empty( $this->servers );
	}

	/**
	 * Whether collect() has already been called.
	 *
	 * @return bool
	 */
	public function is_collected(): bool {
		return $this->collected;
	}

	/**
	 * Whether the MCP Adapter plugin class is loaded and available.
	 *
	 * @return bool
	 */
	public static function is_adapter_available(): bool {
		return class_exists( '\\WP\\MCP\\Core\\McpAdapter' );
	}

	/**
	 * Convenience bootstrap: registers collect() on `rest_api_init` at
	 * priority 20 so consuming plugins can call this once at plugin load time.
	 *
	 * Example:
	 *   add_action( 'plugins_loaded', [ \WPBoilerplate\McpServersList\McpServersList::class, 'bootstrap' ] );
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		add_action( 'rest_api_init', array( self::instance(), 'collect' ), 20 );
	}

	/**
	 * Prevent cloning of the singleton.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the singleton.
	 *
	 * @throws \RuntimeException Always.
	 */
	public function __wakeup(): void {
		throw new \RuntimeException( 'McpServersList singleton cannot be unserialized.' );
	}
}
