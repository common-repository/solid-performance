<?php
/**
 * The provider for the view system.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\View;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\View\Contracts\View;
use SolidWP\Performance\View\Renderers\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for the view system.
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->singleton(
			WordPress::class,
			new WordPress( __DIR__ . '/../../views' )
		);

		$this->container->bind( View::class, $this->container->get( WordPress::class ) );
	}
}
