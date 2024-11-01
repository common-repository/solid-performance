<?php
/**
 * Prevents requests with specific defined constants from being cached.
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
 * Prevent a request with specific defined constants from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Constant implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		// Allow site to prevent caching for specific pages.
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return false;
		}

		// Don't cache cron requests.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		}

		// Don't cache ajax responses.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		// Don't cache REST API responses.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return $next( $context );
	}
}
