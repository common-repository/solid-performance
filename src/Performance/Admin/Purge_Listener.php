<?php
/**
 * Handles admin related cache purging.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Admin;

use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\StellarWP\SuperGlobals\SuperGlobals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin related cache purging.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */
class Purge_Listener {

	/**
	 * @var Batch_Purger
	 */
	private Batch_Purger $batch_purger;

	/**
	 * @param  Batch_Purger $batch_purger  The batch purger.
	 */
	public function __construct( Batch_Purger $batch_purger ) {
		$this->batch_purger = $batch_purger;
	}

	/**
	 * Purges all pages from the page cache.
	 *
	 * @since 0.1.0
	 *
	 * @action init
	 *
	 * @return void
	 */
	public function purge_page_cache(): void {
		$should_purge = filter_input( INPUT_GET, Admin_Bar::PURGE_CACHE_ID, FILTER_VALIDATE_BOOLEAN ) ?? false;

		if ( ! $should_purge ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage the cache on this site.', 'solid-performance' ) );
		}

		$nonce = sanitize_text_field( SuperGlobals::get_get_var( '_wpnonce', '' ) );

		if ( wp_verify_nonce( $nonce, Admin_Bar::NONCE_ACTION ) === false ) {
			$this->redirect();
		}

		// Purge all cached pages.
		$this->batch_purger->queue_purge_all();

		$this->redirect();
	}

	/**
	 * Purges a single page from the cache.
	 *
	 * @since 1.0.0
	 *
	 * @action init
	 *
	 * @return void
	 */
	public function purge_single_page_cache(): void {
		$should_purge = filter_input( INPUT_GET, Admin_Bar::PURGE_CACHE_CURRENT_ID, FILTER_VALIDATE_BOOLEAN ) ?? false;

		if ( ! $should_purge ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage the cache on this site.', 'solid-performance' ) );
		}

		$nonce = sanitize_text_field( SuperGlobals::get_get_var( '_wpnonce', '' ) );

		if ( wp_verify_nonce( $nonce, Admin_Bar::NONCE_ACTION ) === false ) {
			$this->redirect();
		}

		// Grab the base64/rawlurl encoded URI.
		$id = SuperGlobals::get_get_var( Admin_Bar::ID, false );

		if ( ! $id ) {
			$this->redirect();
		}

		// Decode the URI.
		$uri = base64_decode( rawurldecode( $id ) );

		// Make sure this is at least a valid URL before passing it along.
		if ( ! $uri || ! filter_var( $uri, FILTER_VALIDATE_URL ) ) {
			$this->redirect();
		}

		$this->batch_purger->queue(
			Permalink::from(
				[
					'permalink' => $uri,
				]
			)
		);

		$this->redirect();
	}

	/**
	 * Remove query variables set by our methods above and redirect back.
	 *
	 * @return never
	 */
	private function redirect(): void {
		wp_safe_redirect(
			remove_query_arg(
				[
					'_wpnonce',
					Admin_Bar::PURGE_CACHE_CURRENT_ID,
					Admin_Bar::PURGE_CACHE_ID,
					Admin_Bar::ID,
				]
			)
		);

		exit;
	}
}
