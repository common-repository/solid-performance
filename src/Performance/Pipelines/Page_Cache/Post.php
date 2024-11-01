<?php
/**
 * Prevent excluded posts from being saved to the cache.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\Admin\Post_Cache_Exclusion;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent excluded posts from being saved to the cache.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Post implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		$exclude_post = get_post_meta( $context->get_post_id(), Post_Cache_Exclusion::META_KEY, true );

		if ( $exclude_post ) {
			return false;
		}

		return $next( $context );
	}
}
