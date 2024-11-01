<?php
/**
 * Display an admin notice if permalinks aren't enabled.
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
 * Display an admin notice if permalinks aren't enabled.
 *
 * @package SolidWP\Performance
 */
final class Permalink {

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
	 * Queue the notice to be rendered if permalinks are disabled.
	 *
	 * @action admin_notices
	 *
	 * @return void
	 */
	public function queue(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( get_option( 'permalink_structure' ) ) ) {
			return;
		}

		$message = sprintf(
			// translators: 1. Permalinks URL.
			__(
				'<strong>Solid Performance</strong> depends on a custom permalink structure. Please enable this in your <a href="%1$s">Permalink Settings</a> to begin speeding up your website.',
				'solid-performance'
			),
			esc_url( admin_url( 'options-permalink.php' ) )
		);

		$notice = new Notice( Notice::ERROR, $message );

		$this->notice_handler->add( $notice );
	}
}
