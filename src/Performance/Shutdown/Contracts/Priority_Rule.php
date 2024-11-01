<?php
/**
 * The interface for defining priority rules.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown\Contracts;

interface Priority_Rule {

	/**
	 * Determine if the rule applies and get its priority.
	 *
	 * @return int|null Returns the priority if the rule applies, otherwise null.
	 */
	public function get_priority(): ?int;
}
