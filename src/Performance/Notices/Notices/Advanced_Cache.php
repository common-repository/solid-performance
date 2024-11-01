<?php
/**
 * Display an admin notice if our advanced cache is missing.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Notices\Notices;

use SolidWP\Performance\Notices\Notice;
use SolidWP\Performance\Notices\Notice_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display an admin notice if our advanced cache is missing.
 *
 * @package SolidWP\Performance
 */
final class Advanced_Cache {

	/**
	 * @var Notice_Handler
	 */
	private Notice_Handler $notice_handler;

	/**
	 * @param  Notice_Handler $notice_handler The notice handler.
	 */
	public function __construct( Notice_Handler $notice_handler ) {
		$this->notice_handler = $notice_handler;
	}

	/**
	 * Queue the notice to be rendered if advanced-cache.php is missing.
	 *
	 * @action admin_notices
	 *
	 * @return void
	 */
	public function queue(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// This notice shouldn't show if WP_CACHE is not set to true.
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			return;
		}

		// If this define doesn't exist, advanced-cache.php is invalid or missing.
		if ( defined( 'SWPSP_ADVANCED_CACHE_VERSION' ) ) {
			return;
		}

		$message = sprintf(
			// translators: 1. Solid Performance Settings URL.
			__(
				'<strong>Solid Performance is not caching!</strong> visit the <a href="%1$s">Settings Page</a> and regenerate the advanced-cache.php file under the <em>Advanced</em> tab.',
				'solid-performance'
			),
			esc_url( admin_url( 'options-general.php?page=swpsp-settings' ) )
		);

		$notice = new Notice( Notice::ERROR, $message, true, 'solidwp-performance-inactive' );

		$this->notice_handler->add( $notice );
	}
}
