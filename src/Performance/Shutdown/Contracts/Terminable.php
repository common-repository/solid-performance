<?php
/**
 * Implement this interface for a Terminable task, which will
 * execute on the shutdown action.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown\Contracts;

interface Terminable {

	/**
	 * Perform a task on shutdown.
	 *
	 * @action shutdown
	 *
	 * @return void
	 */
	public function terminate(): void;
}
