<?php
/**
 * Header trait for shared compressor functionality.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression\Traits;

use SolidWP\Performance\Page_Cache\Compression\Contracts\Compressible;

/**
 * Header trait for shared compressor functionality.
 *
 * @mixin Compressible
 */
trait With_Headers {

	/**
	 * Send Content-Encoding headers for the current compression strategy.
	 *
	 * @return void
	 */
	public function send_headers(): void {
		header( sprintf( 'Content-Encoding: %s', $this->encoding() ) );
	}
}
