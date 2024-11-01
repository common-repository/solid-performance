<?php
/**
 * Prevents requests to the admin from being cached.
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
 * Prevent a request to the admin from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Admin implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		// Do not cache any request to the admin.
		if ( strpos( $context->get_request()->path, 'wp-admin' ) !== false ) {
			return false;
		}

		return $next( $context );
	}
}
