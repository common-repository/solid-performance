<?php
/**
 * All functionality related to deactivating the plugin.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Plugin;

use SolidWP\Performance\Config\Advanced_Cache;
use SolidWP\Performance\Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles actions that should run when the plugin is deactivated.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
final class Deactivator {

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
	 * Deactivation hook.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function __invoke(): void {
		$this->remove_advanced_cache();
	}

	/**
	 * Remove the generated advanced-cache.php drop-in.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function remove_advanced_cache(): void {
		$this->container->get( Advanced_Cache::class )->remove();
	}
}
