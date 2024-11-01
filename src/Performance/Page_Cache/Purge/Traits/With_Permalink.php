<?php
/**
 * Permalink related helper methods.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Traits;

/**
 * Permalink related helper methods.
 *
 * @package SolidWP\Performance
 */
trait With_Permalink {

	/**
	 * Whether a Permalink is in a pretty permalink state.
	 *
	 * @param  string $permalink The permalink URI.
	 *
	 * @return bool
	 */
	private function is_pretty( string $permalink ): bool {
		return ! str_contains( $permalink, '/?' );
	}
}
