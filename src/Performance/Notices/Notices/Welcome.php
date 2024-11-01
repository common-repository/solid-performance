<?php
/**
 * Handles all functionality related to the Welcome admin notice.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Notices\Notices;

use SolidWP\Performance\Notices\Notice;
use SolidWP\Performance\Notices\Notice_Handler;
use SolidWP\Performance\View\Contracts\View;
use SolidWP\Performance\View\Exceptions\FileNotFoundException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all functionality related to the Welcome admin notice.
 *
 * @since 0.1.0
 *
 * @package StellarWP\Performance
 */
class Welcome {

	public const NOTICE_OPTION = 'solid-performance-welcome-notice';
	public const VIEW          = 'notices/welcome';

	/**
	 * @var Notice_Handler
	 */
	private Notice_Handler $notice_handler;

	/**
	 * @var View
	 */
	private View $view;

	/**
	 * @param  Notice_Handler $notice_handler The notice handler.
	 * @param  View           $view The view renderer.
	 */
	public function __construct( Notice_Handler $notice_handler, View $view ) {
		$this->notice_handler = $notice_handler;
		$this->view           = $view;
	}

	/**
	 * Renders the welcome notice only on first plugin activation.
	 *
	 * @since 0.1.0
	 *
	 * @throws FileNotFoundException If the view file is not found.
	 *
	 * @return void
	 */
	public function maybe_render(): void {
		// Notice has already been displayed.
		if ( $this->has_been_shown() ) {
			return;
		}

		// Only users able to use the settings page should see the notice.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message = $this->view->render_to_string( self::VIEW );

		$notice = new Notice( 'info', $message, true, 'solid-performance-welcome-notice', [], [], false );
		$this->notice_handler->add( $notice );

		update_option( self::NOTICE_OPTION, true );
	}

	/**
	 * Determines if the Welcome notice has already been shown on activation.
	 *
	 * @return bool
	 */
	public function has_been_shown(): bool {
		return get_option( self::NOTICE_OPTION ) !== false;
	}

	/**
	 * Load the welcome notice styles.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'solid-performance-welcome-notice',
			trailingslashit( plugin_dir_url( SWPSP_PLUGIN_FILE ) ) . 'build/notices.css',
			[],
			'0.1.0'
		);
	}
}
