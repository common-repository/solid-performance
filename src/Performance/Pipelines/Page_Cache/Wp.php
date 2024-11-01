<?php
/**
 * WordPress specific cache saving bypasses.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress specific cache saving bypasses.
 *
 * @note This can't be used to bypass serving as it's too early in execution.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */
class Wp implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {
		if (
			is_robots() ||
			is_feed() ||
			( post_password_required() && ! is_front_page() && ! is_archive() ) ||
			empty( get_option( 'permalink_structure' ) )
		) {
			return false;
		}

		return $next( $context );
	}
}
