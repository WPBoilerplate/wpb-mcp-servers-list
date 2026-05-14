<?php
/**
 * Tool data object.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList\Data;

/**
 * Immutable value object representing a single MCP tool.
 */
class ToolData implements \JsonSerializable {

	/**
	 * Tool name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Tool description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Constructor.
	 *
	 * @param string $name        Tool name.
	 * @param string $description Tool description.
	 */
	public function __construct( string $name, string $description ) {
		$this->name        = $name;
		$this->description = $description;
	}

	/**
	 * Get tool name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get tool description.
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
