<?php
/**
 * The config writer interface.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config\Writers\Contracts;

interface Writable {

	/**
	 * Save the config and return whether it was successful or not.
	 *
	 * @return bool
	 */
	public function save(): bool;
}
