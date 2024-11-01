<?php
/**
 * The provider for all core Page caching related functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Pipelines;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\StellarWP\Pipeline\Pipeline;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all core Page caching related functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->when( Pipeline::class )
						->needs( '$container' )
						->give( $this->container );
	}
}
