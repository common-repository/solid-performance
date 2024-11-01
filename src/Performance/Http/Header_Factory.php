<?php
/**
 * Creates a header instance with response headers.
 *
 * @since   1.0.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Http;

use SolidWP\Performance\Container;

/**
 * Creates a header instance using response headers.
 *
 * @since   1.0.0
 *
 * @package SolidWP\Performance
 */
class Header_Factory {

	/**
	 * @var Container
	 */
	private Container $container;

	/**
	 * @param  Container $container The container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Make a header instance using response headers.
	 *
	 * @note headers_list() does not work in CLI.
	 *
	 * @return Header
	 */
	public function make(): Header {
		$headers = headers_list();

		$this->container->when( Header::class )
						->needs( '$headers' )
						->give( static fn(): array => $headers );

		$header = $this->container->get( Header::class );

		$this->container->singleton( Header::class, $header );

		return $header;
	}
}
