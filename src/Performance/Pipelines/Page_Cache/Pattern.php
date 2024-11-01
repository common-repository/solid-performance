<?php
/**
 * Prevent requests from being saved to the cache when they match specified regex patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP|Performance
 */

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent caching requests that match specific regex patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
abstract class Pattern implements Pipe {

	/**
	 * Gets an array of regex patterns to check.
	 *
	 * @since 0.1.0
	 *
	 * @return array<int,string>
	 */
	abstract public function get_patterns(): array;

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		$patterns = $this->get_patterns();

		// No exclusions, skip to the next pipe.
		if ( ! is_array( $patterns ) || count( $patterns ) === 0 ) {
			return $next( $context );
		}

		$request_path = $context->get_request()->path;

		foreach ( $patterns as $pattern ) {
			// If the pattern matches the request path directly.
			if ( $request_path === $pattern ) {
				return false;
			}

			$result = preg_match( '#' . $pattern . '#', $request_path );

			if ( $result === 1 ) {
				return false;
			}
		}

		return $next( $context );
	}
}
