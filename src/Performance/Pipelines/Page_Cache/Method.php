<?php
/**
 * Prevents caching requests based on method used.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\Http\Request;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent caching requests based on method used.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Method implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {
		/** @var Request */
		$request = $context->get_request();
		if ( $request->method !== 'GET' && $request->method !== 'HEAD' ) {
			return false;
		}

		return $next( $context );
	}
}
