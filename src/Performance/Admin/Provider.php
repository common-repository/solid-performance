<?php
/**
 * The provider hooking Admin class methods to WordPress events.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Admin;

use SolidWP\Performance\Contracts\Service_Provider;

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

	public const PUBLIC_POST_TYPES = 'solidwp.performance.admin.public_post_types';

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->singleton( Settings_Page::class, Settings_Page::class );

		add_filter( 'admin_bar_menu', $this->container->callback( Admin_Bar::class, 'add_purge_admin_bar_link' ), 100 );
		add_filter( 'init', $this->container->callback( Purge_Listener::class, 'purge_page_cache' ) );
		add_filter( 'init', $this->container->callback( Purge_Listener::class, 'purge_single_page_cache' ) );
		add_filter( 'admin_menu', $this->container->callback( Settings_Page::class, 'add_settings_page' ), 100 );
		add_action( 'admin_init', $this->container->callback( Settings_Page::class, 'register_settings' ) );
		add_action( 'rest_api_init', $this->container->callback( Settings_Page::class, 'register_settings' ) );
		add_action( 'plugin_action_links_' . plugin_basename( SWPSP_PLUGIN_FILE ), $this->container->callback( Settings_Page::class, 'settings_link' ), 10 );

		$this->register_page_cache_exclusion();
	}

	/**
	 * Registers the page cache exclusion meta box.
	 *
	 * @return void
	 */
	private function register_page_cache_exclusion(): void {
		// Exclude the meta box from these post types.
		$post_types_to_ignore = (array) apply_filters(
			'solidwp/performance/public_post_type_ignore_array',
			[
				// Elementor.
				'elementor_library',
				// Beaver Builder.
				'fl-theme-layout',
				// WooCommerce.
				'shop_order',
				// Kadence.
				'kadence_element',
				'kadence_conversions',
				'kadence_wootemplate',
				'ele-product-template',
				'ele-p-arch-template',
				'ele-p-loop-template',
				'ele-check-template',
				'kt_size_chart',
				'kt_cart_notice',
				'kt_reviews',
				'kt_product_tabs',
				// Jet.
				'jet-menu',
				'jet-popup',
				'jet-smart-filters',
				'jet-theme-core',
				'jet-woo-builder',
				'jet-engine',
				// LifterLMS.
				'llms_certificate',
				'llms_my_certificate',
				// LearnDash.
				'sfwd-certificates',
				'sfwd-transactions',
				'reply',
			]
		);

		$this->container->when( Post_Cache_Exclusion::class )
						->needs( '$post_types_to_ignore' )
						->give( $post_types_to_ignore );

		// Get all public, non-built in post types and manually add post+page.
		$this->container->singleton(
			self::PUBLIC_POST_TYPES,
			static fn() => (array) apply_filters(
				'solidwp/performance/public_post_type_objects',
				array_merge(
					get_post_types(
						[
							'public'   => true,
							'_builtin' => false,
						],
					),
					[ 'post', 'page' ]
				)
			)
		);

		$this->container->when( Post_Cache_Exclusion::class )
						->needs( '$post_types' )
						->give( $this->container->get( self::PUBLIC_POST_TYPES ) );

		$this->container->singleton( Post_Cache_Exclusion::class, Post_Cache_Exclusion::class );

		add_action(
			'init',
			static function (): void {
				Post_Cache_Exclusion::register_meta();
			},
			20
		);
		add_action( 'admin_init', $this->container->callback( Post_Cache_Exclusion::class, 'register_meta_script' ) );
		add_action( 'enqueue_block_editor_assets', $this->container->callback( Post_Cache_Exclusion::class, 'script_enqueue' ) );

		// Classic Editor meta box.
		foreach ( [ 'load-post.php', 'load-post-new.php' ] as $action ) {
			add_action(
				$action,
				function () {
					add_action( 'add_meta_boxes', $this->container->callback( Post_Cache_Exclusion::class, 'add_metabox' ) );
					add_action( 'save_post', $this->container->callback( Post_Cache_Exclusion::class, 'save_metabox' ), 10, 1 );
				}
			);
		}
	}
}
