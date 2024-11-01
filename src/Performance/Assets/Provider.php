<?php
/**
 * Registers asset related functionality in the container.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Assets;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Core;

/**
 * Registers asset related functionality in the container.
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->container->when( Asset::class )
						->needs( '$plugin_url' )
						->give( static fn( $c ) => $c->get( Core::PLUGIN_URL ) );
		$this->container->when( Asset::class )
						->needs( '$plugin_dir' )
						->give( static fn( $c ) => $c->get( Core::PLUGIN_DIR ) );

		$this->container->singleton( Asset::class, Asset::class );
	}
}
