<?php
/**
 * Handles all functionality related to the Admin Bar.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Admin;

use SolidWP\Performance\Http\Request;
use WP_Admin_Bar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all functionality related to the Admin Bar.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Admin_Bar {

	public const MENU_ID                = 'swpsp_cache';
	public const PURGE_CACHE_ID         = 'swpsp_cache_purge';
	public const PURGE_CACHE_CURRENT_ID = 'swpsp_cache_purge_current';
	public const NONCE_ACTION           = 'swpsp_purge_page_cache';
	public const ID                     = 'swpsp_id';

	/**
	 * Adds a new menu item to the admin bar for purging cache.
	 *
	 * @since 0.1.0
	 *
	 * @action admin_bar_menu
	 *
	 * @param WP_Admin_Bar $admin_bar An instance of the admin bar to adjust.
	 *
	 * @return void
	 */
	public function add_purge_admin_bar_link( WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$admin_bar->add_menu(
			[
				'id'     => self::MENU_ID,
				'parent' => null,
				'title'  => esc_html__( 'Solid Performance', 'solid-performance' ),
			]
		);

		$admin_bar->add_menu(
			[
				'id'     => self::PURGE_CACHE_ID,
				'parent' => self::MENU_ID,
				'title'  => esc_html__( 'Clear Page Cache', 'solid-performance' ),
				'href'   => add_query_arg(
					[
						self::PURGE_CACHE_ID => true,
						'_wpnonce'           => wp_create_nonce( self::NONCE_ACTION ),
					]
				),
			]
		);

		if ( ! is_admin() && ! wp_doing_ajax() && ! is_404() ) {
			$request = new Request();

			$admin_bar->add_menu(
				[
					'id'     => self::PURGE_CACHE_CURRENT_ID,
					'parent' => self::MENU_ID,
					'title'  => esc_html__( 'Clear Current Page', 'solid-performance' ),
					'href'   => add_query_arg(
						[
							self::PURGE_CACHE_CURRENT_ID => true,
							self::ID                     => rawurlencode( base64_encode( strtok( $request->uri, '?' ) ) ),
							'_wpnonce'                   => wp_create_nonce( self::NONCE_ACTION ),
						]
					),
				]
			);
		}
	}
}
