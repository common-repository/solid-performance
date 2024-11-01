<?php
/**
 * Display an admin notice if WP_CACHE is set to false.
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
 * Display an admin notice if WP_CACHE is set to false.
 *
 * @package SolidWP\Performance
 */
final class Wp_Cache_Constant {

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
	 * Queue the notice to be rendered if WP_CACHE is set to false.
	 *
	 * @action admin_notices
	 *
	 * @return void
	 */
	public function queue(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// WP_CACHE is true, no need to display the notice.
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return;
		}

		$message = sprintf(
			// translators: 1. define( 'WP_CACHE', true );.
			__(
				'<strong>Solid Performance is not caching!</strong> <code>%1$s</code> is missing or set to false in wp-config.php.',
				'solid-performance'
			),
			'define( \'WP_CACHE\', true );'
		);

		$notice = new Notice( Notice::WARNING, $message, true, 'solidwp-performance-inactive' );

		$this->notice_handler->add( $notice );
	}
}
