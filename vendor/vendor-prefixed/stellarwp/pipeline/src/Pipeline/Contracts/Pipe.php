<?php
/**
 * @license GPL-2.0
 *
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare( strict_types = 1 );

namespace SolidWP\Performance\StellarWP\Pipeline\Contracts;

use Closure;

/**
 * Interface PipeInterface
 *
 * @package SolidWP\Performance\StellarWP\Pipeline\Contracts
 */
interface Pipe {
	/**
	 * Handle the given value.
	 *
	 * @param mixed   $passable The value to handle.
	 * @param Closure $next     The next pipe in the pipeline.
	 *
	 * @return mixed
	 */
	public function handle( $passable, Closure $next );
}
