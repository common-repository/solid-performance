<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Notices;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Notices\Notices\Advanced_Cache;
use SolidWP\Performance\Notices\Notices\Permalink;
use SolidWP\Performance\Notices\Notices\Welcome;
use SolidWP\Performance\Notices\Notices\Wp_Cache_Constant;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all Admin related functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		add_action( 'admin_notices', $this->container->callback( Permalink::class, 'queue' ), 1 );
		add_action( 'admin_notices', $this->container->callback( Wp_Cache_Constant::class, 'queue' ), 1 );
		add_action( 'admin_notices', $this->container->callback( Advanced_Cache::class, 'queue' ), 1 );
		add_action( 'admin_notices', $this->container->callback( Notice_Handler::class, 'display' ), 100 );

		$this->set_up_welcome_notice();
	}

	/**
	 * Only hooks functionality in when the Welcome notice should be displayed.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function set_up_welcome_notice(): void {
		if ( ! $this->container->get( Welcome::class )->has_been_shown() ) {
			add_filter( 'init', $this->container->callback( Welcome::class, 'maybe_render' ), 10 );
			add_filter( 'admin_enqueue_scripts', $this->container->callback( Welcome::class, 'enqueue_styles' ), 10 );
		}
	}
}
