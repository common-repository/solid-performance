<?php
/**
 * The provider for all core Page caching related functionality.
 *
 * @since   0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Page_Cache;

use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Page_Cache\Compression\Collection;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Brotli;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Deflate;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Gzip;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Html;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Zstd;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all core Page caching related functionality.
 *
 * @since   0.1.0
 *
 * @package SolidWP\Performance
 */
class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->when( Expiration::class )
						->needs( '$seconds' )
						->give( static fn( $c ): int => $c->get( Config::class )->get( 'page_cache.expiration' ) );

		$this->container->when( WP_Context::class )
						->needs( '$post_id' )
						->give( static fn(): int => (int) get_the_ID() );

		$this->container->when( Collection::class )
						->needs( '$compressors' )
						->give(
							static fn( $c ): array => [
								// Different browser compression algorithms for saving and serving cached files.
								$c->get( Brotli::class ),
								$c->get( Zstd::class ),
								$c->get( Gzip::class ),
								$c->get( Deflate::class ),
								$c->get( Html::class ),
							]
						);

		$this->container->singleton( Collection::class, Collection::class );
	}
}
