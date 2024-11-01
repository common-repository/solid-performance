<?php
/**
 * Bypass caching if not the allowed content type.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Pipelines\Page_Cache;

use Closure;
use SolidWP\Performance\Http\Header_Factory;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bypass caching if not the allowed content type.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */
class Content_Type implements Pipe {

	/**
	 * @var Header_Factory
	 */
	private Header_Factory $header_factory;

	/**
	 * @param  Header_Factory $factory  The header factory.
	 */
	public function __construct( Header_Factory $factory ) {
		$this->header_factory = $factory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {
		$header = $this->header_factory->make();

		// Only allow text/html content type responses to create cache files.
		if ( $header->starts_with( 'content-type', 'text/html;' ) ) {
			return $next( $context );
		}

		return false;
	}
}
