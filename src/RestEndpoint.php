<?php
/**
 * Optional REST API endpoint for exposing MCP server data over HTTP.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList;

use WPBoilerplate\McpServersList\Data\ServerData;

/**
 * Registers a read-only REST API endpoint that returns all collected MCP
 * server data as JSON.
 *
 * Access is restricted to WordPress administrators (`manage_options`) by
 * default. The required capability can be overridden at runtime via the
 * `wpb_mcp_servers_list_rest_capability` filter.
 *
 * This class does not self-register — the consuming plugin decides when and
 * whether to call RestEndpoint::register().
 *
 * Default endpoint: GET /wp-json/wpb-mcp-servers-list/v1/servers
 *
 * Usage in a consuming plugin:
 *
 *   add_action( 'rest_api_init', function () {
 *       \WPBoilerplate\McpServersList\RestEndpoint::register();
 *   }, 20 );
 *
 * Override the required capability via filter:
 *
 *   add_filter( 'wpb_mcp_servers_list_rest_capability', function ( $cap ) {
 *       return 'edit_posts'; // allow editors
 *   } );
 *
 * Custom namespace / capability at registration time:
 *
 *   RestEndpoint::register( 'manage_options', 'my-plugin/v1', '/mcp-servers' );
 */
class RestEndpoint {

	/**
	 * Default REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'wpb-mcp-servers-list/v1';

	/**
	 * Default REST route.
	 *
	 * @var string
	 */
	const ROUTE = '/servers';

	/**
	 * Register the REST endpoint.
	 *
	 * Must be called during the `rest_api_init` action at priority 20+ so
	 * that McpAdapter (priority 15) has already initialised the servers.
	 *
	 * @param string $capability  WordPress capability required to access the endpoint. Default 'manage_options'.
	 * @param string $namespace   REST namespace. Default self::NAMESPACE.
	 * @param string $route       REST route. Default self::ROUTE.
	 * @return void
	 */
	public static function register(
		string $capability = 'manage_options',
		string $namespace = self::NAMESPACE,
		string $route = self::ROUTE
	): void {
		register_rest_route(
			$namespace,
			$route,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => static function () {
					$list = McpServersList::instance();

					// Collect if not already done (e.g. endpoint called standalone).
					if ( ! $list->is_collected() ) {
						$list->collect();
					}

					return new \WP_REST_Response(
						array(
							'adapter_available' => McpServersList::is_adapter_available(),
							'servers'           => array_map(
								static function ( ServerData $server ) {
									return $server->to_array();
								},
								$list->get_servers()
							),
						),
						200
					);
				},
				'permission_callback' => static function () use ( $capability ) {
					/**
					 * Filters the WordPress capability required to access the MCP servers
					 * list REST endpoint.
					 *
					 * Defaults to 'manage_options' (administrators only). Return a different
					 * capability string to broaden or restrict access.
					 *
					 * Example — allow editors:
					 *   add_filter( 'wpb_mcp_servers_list_rest_capability', fn() => 'edit_posts' );
					 *
					 * Example — allow any logged-in user:
					 *   add_filter( 'wpb_mcp_servers_list_rest_capability', fn() => 'read' );
					 *
					 * @since 1.0.1
					 *
					 * @param string $capability The required capability. Default 'manage_options'.
					 */
					$required = (string) apply_filters( 'wpb_mcp_servers_list_rest_capability', $capability );

					return current_user_can( $required );
				},
				'schema'              => array( self::class, 'get_schema' ),
			)
		);
	}

	/**
	 * JSON Schema for the endpoint response.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_schema(): array {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpb-mcp-servers-list',
			'type'       => 'object',
			'properties' => array(
				'adapter_available' => array(
					'description' => 'Whether the MCP Adapter plugin is active.',
					'type'        => 'boolean',
				),
				'servers'           => array(
					'description' => 'List of registered MCP servers.',
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'              => array( 'type' => 'string' ),
							'name'            => array( 'type' => 'string' ),
							'description'     => array( 'type' => 'string' ),
							'version'         => array( 'type' => 'string' ),
							'endpoint_url'    => array( 'type' => 'string', 'format' => 'uri' ),
							'route_namespace' => array( 'type' => 'string' ),
							'route'           => array( 'type' => 'string' ),
							'tools'           => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'name'        => array( 'type' => 'string' ),
										'description' => array( 'type' => 'string' ),
									),
								),
							),
							'resources'       => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'name'        => array( 'type' => 'string' ),
										'uri'         => array( 'type' => 'string' ),
										'description' => array( 'type' => 'string' ),
									),
								),
							),
							'prompts'         => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'name'        => array( 'type' => 'string' ),
										'description' => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
				),
			),
		);
	}
}
