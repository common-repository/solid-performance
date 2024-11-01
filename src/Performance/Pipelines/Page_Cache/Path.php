<?php
/**
 * Prevents requests with specific paths from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent a request with specific paths from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Path implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		// Do not cache any requests to the login page.
		if ( trim( $context->get_request()->path, '/' ) === 'wp-login.php' ) {
			return false;
		}

		return $next( $context );
	}
}
