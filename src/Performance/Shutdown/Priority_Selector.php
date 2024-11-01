<?php
/**
 * Determines the initial priority the shutdown handler runs on.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown;

use SolidWP\Performance\Shutdown\Contracts\Priority_Rule;

/**
 * Determines the initial priority the shutdown handler runs on.
 *
 * @package SolidWP\Performance
 */
final class Priority_Selector {

	public const DEFAULT_PRIORITY = 1;

	/**
	 * @var Priority_Rule[]
	 */
	private array $rules;

	/**
	 * @param  Priority_Rule[] $rules The priority rules to process.
	 */
	public function __construct( array $rules ) {
		$this->rules = $rules;
	}

	/**
	 * Get the initial shutdown hook priority, overridden if certain
	 * rules return a priority.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		foreach ( $this->rules as $rule ) {
			$priority = $rule->get_priority();

			if ( $priority !== null ) {
				return $priority;
			}
		}

		return self::DEFAULT_PRIORITY;
	}
}
