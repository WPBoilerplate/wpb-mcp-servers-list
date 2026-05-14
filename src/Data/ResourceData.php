<?php
/**
 * Resource data object.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList\Data;

/**
 * Immutable value object representing a single MCP resource.
 */
class ResourceData implements \JsonSerializable {

	/**
	 * Resource name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Resource URI.
	 *
	 * @var string
	 */
	private $uri;

	/**
	 * Resource description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Constructor.
	 *
	 * @param string $name        Resource name.
	 * @param string $uri         Resource URI.
	 * @param string $description Resource description.
	 */
	public function __construct( string $name, string $uri, string $description ) {
		$this->name        = $name;
		$this->uri         = $uri;
		$this->description = $description;
	}

	/**
	 * Get resource name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get resource URI.
	 *
	 * @return string
	 */
	public function get_uri(): string {
		return $this->uri;
	}

	/**
	 * Get resource description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Convert to array.
	 *
	 * @return array<string, string>
	 */
	public function to_array(): array {
		return array(
			'name'        => $this->name,
			'uri'         => $this->uri,
			'description' => $this->description,
		);
	}

	/**
	 * JSON serialization.
	 *
	 * @return array<string, string>
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->to_array();
	}
}
