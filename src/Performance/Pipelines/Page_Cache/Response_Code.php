<?php
/**
 * Prevent 404 responses from being cached.
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
 * Prevent non-2xx responses from being cached.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */
class Response_Code implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {
		$code = http_response_code();

		if ( $code >= 200 && $code < 300 ) {
			return $next( $context );
		}

		return false;
	}
}
