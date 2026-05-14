<?php
/**
 * Server data object.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList\Data;

/**
 * Immutable value object representing a registered MCP server and all its components.
 */
class ServerData implements \JsonSerializable {

	/**
	 * Server ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Human-readable server name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Server description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Server version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Full REST API endpoint URL for this server.
	 *
	 * @var string
	 */
	private $endpoint_url;

	/**
	 * REST route namespace (e.g. "wp/mcp/v1").
	 *
	 * @var string
	 */
	private $route_namespace;

	/**
	 * REST route path (e.g. "/mcp").
	 *
	 * @var string
	 */
	private $route;

	/**
	 * Registered tools.
	 *
	 * @var ToolData[]
	 */
	private $tools;

	/**
	 * Registered resources.
	 *
	 * @var ResourceData[]
	 */
	private $resources;

	/**
	 * Registered prompts.
	 *
	 * @var PromptData[]
	 */
	private $prompts;

	/**
	 * Constructor.
	 *
	 * @param string         $id              Server ID.
	 * @param string         $name            Server name.
	 * @param string         $description     Server description.
	 * @param string         $version         Server version.
	 * @param string         $endpoint_url    Full REST endpoint URL.
	 * @param string         $route_namespace REST route namespace.
	 * @param string         $route           REST route path.
	 * @param ToolData[]     $tools           Registered tools.
	 * @param ResourceData[] $resources       Registered resources.
	 * @param PromptData[]   $prompts         Registered prompts.
	 */
	public function __construct(
		string $id,
		string $name,
		string $description,
		string $version,
		string $endpoint_url,
		string $route_namespace,
		string $route,
		array $tools,
		array $resources,
		array $prompts
	) {
		$this->id              = $id;
		$this->name            = $name;
		$this->description     = $description;
		$this->version         = $version;
		$this->endpoint_url    = $endpoint_url;
		$this->route_namespace = $route_namespace;
		$this->route           = $route;
		$this->tools           = $tools;
		$this->resources       = $resources;
		$this->prompts         = $prompts;
	}

	/**
	 * Build a ServerData instance from a live McpServer object.
	 *
	 * This method is intentionally un-typed for $server so the package does not
	 * declare a hard dependency on mcp-adapter. It is only called after confirming
	 * the WP\MCP\Core\McpAdapter class exists.
	 *
	 * @param \WP\MCP\Core\McpServer $server MCP server instance.
	 * @return self
	 */
	public static function from_mcp_server( $server ): self {
		$tools = array();
		foreach ( $server->get_tools() as $tool ) {
			$tools[] = new ToolData(
				$tool->getName(),
				$tool->getDescription() ?? ''
			);
		}

		$resources = array();
		foreach ( $server->get_resources() as $resource ) {
			$resources[] = new ResourceData(
				$resource->getName(),
				$resource->getUri(),
				$resource->getDescription() ?? ''
			);
		}

		$prompts = array();
		foreach ( $server->get_prompts() as $prompt ) {
			$prompts[] = new PromptData(
				$prompt->getName(),
				$prompt->getDescription() ?? ''
			);
		}

		$route_namespace = $server->get_server_route_namespace();
		$route           = $server->get_server_route();
		$endpoint_url    = function_exists( 'rest_url' )
			? rest_url( trailingslashit( $route_namespace ) . ltrim( $route, '/' ) )
			: '';

		return new self(
			$server->get_server_id(),
			$server->get_server_name(),
			$server->get_server_description(),
			$server->get_server_version(),
			$endpoint_url,
			$route_namespace,
			$route,
			$tools,
			$resources,
			$prompts
		);
	}

	/**
	 * Get server ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get server name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get server description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Get server version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Get full REST endpoint URL.
	 *
	 * @return string
	 */
	public function get_endpoint_url(): string {
		return $this->endpoint_url;
	}

	/**
	 * Get REST route namespace.
	 *
	 * @return string
	 */
	public function get_route_namespace(): string {
		return $this->route_namespace;
	}

	/**
	 * Get REST route path.
	 *
	 * @return string
	 */
	public function get_route(): string {
		return $this->route;
	}

	/**
	 * Get registered tools.
	 *
	 * @return ToolData[]
	 */
	public function get_tools(): array {
		return $this->tools;
	}

	/**
	 * Get registered resources.
	 *
	 * @return ResourceData[]
	 */
	public function get_resources(): array {
		return $this->resources;
	}

	/**
	 * Get registered prompts.
	 *
	 * @return PromptData[]
	 */
	public function get_prompts(): array {
		return $this->prompts;
	}

	/**
	 * Convert to plain array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'id'              => $this->id,
			'name'            => $this->name,
			'description'     => $this->description,
			'version'         => $this->version,
			'endpoint_url'    => $this->endpoint_url,
			'route_namespace' => $this->route_namespace,
			'route'           => $this->route,
			'tools'           => array_map( static function ( ToolData $t ) {
				return $t->to_array();
			}, $this->tools ),
			'resources'       => array_map( static function ( ResourceData $r ) {
				return $r->to_array();
			}, $this->resources ),
			'prompts'         => array_map( static function ( PromptData $p ) {
				return $p->to_array();
			}, $this->prompts ),
		);
	}

	/**
	 * JSON serialization.
	 *
	 * @return array<string, mixed>
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->to_array();
	}
}
