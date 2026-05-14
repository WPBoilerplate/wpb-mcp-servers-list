<?php
/**
 * Prompt data object.
 *
 * @package WPBoilerplate\McpServersList
 */

declare( strict_types=1 );

namespace WPBoilerplate\McpServersList\Data;

/**
 * Immutable value object representing a single MCP prompt.
 */
class PromptData implements \JsonSerializable {

	/**
	 * Prompt name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Prompt description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Constructor.
	 *
	 * @param string $name        Prompt name.
	 * @param string $description Prompt description.
	 */
	public function __construct( string $name, string $description ) {
		$this->name        = $name;
		$this->description = $description;
	}

	/**
	 * Get prompt name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get prompt description.
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
