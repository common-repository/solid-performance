<?php
/**
 * Prevents requests for logged in users from being cached.
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
 * Prevent requests for logged in users from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Authenticated implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {
		// Convert all cookie keys into a string for searching.
		$keystring = implode( '|', array_keys( $context->get_request()->cookie ) );

		// If the user currently has a logged in cookie, don't serve a cached file.
		if ( strpos( $keystring, 'wordpress_logged_in' ) !== false ) {
			return false;
		}

		// If the user currently has a wp-postpass cookie from a password protected post, bypass cache.
		// COOKIEHASH won't be available when checking for served cache files.
		if ( defined( 'COOKIEHASH' ) && class_exists( 'WP' ) ) {
			if ( strpos( $keystring, 'wp-postpass_' . COOKIEHASH ) !== false ) {
				return false;
			}
		}

		return $next( $context );
	}
}
