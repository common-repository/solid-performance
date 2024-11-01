<?php
/**
 * Prevents requests with a query from being cached.
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
 * Prevent a request with a query from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Query implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		if ( strlen( $context->get_request()->query ) > 0 ) {
			return false;
		}

		return $next( $context );
	}
}
