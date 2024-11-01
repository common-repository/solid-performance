<?php
/**
 * The provider for all purge related functionality.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Author_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Archive_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Home_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Menu_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Option_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Post_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Template_Purger;
use SolidWP\Performance\Page_Cache\Purge\Purgers\Term_Purger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all purge related functionality.
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->register_batch_purger();
		$this->register_post_purger();
		$this->register_author_purger();
		$this->register_term_purger();
		$this->register_archive_purger();
		$this->register_home_purger();
		$this->register_option_purger();
		$this->register_template_purger();
		$this->register_menu_purger();
	}

	/**
	 * Register the batch purger.
	 *
	 * @return void
	 */
	private function register_batch_purger(): void {
		$this->container->singleton( Batch_Purger::class, Batch_Purger::class );
	}

	/**
	 * Register the post purger.
	 *
	 * @return void
	 */
	private function register_post_purger(): void {
		$this->container->singleton( Post_Purger::class, Post_Purger::class );

		add_action( 'pre_post_update', $this->container->callback( Post_Purger::class, 'capture' ), 1, 2 );
		add_filter( 'pre_trash_post', $this->container->callback( Post_Purger::class, 'capture_trashed' ), 1, 3 );
		add_action( 'transition_post_status', $this->container->callback( Post_Purger::class, 'on_transition' ), 20, 3 );
		add_action( 'before_delete_post', $this->container->callback( Post_Purger::class, 'on_delete' ), 20, 1 );
		add_action( 'wp_update_comment_count', $this->container->callback( Post_Purger::class, 'on_comment_count' ), 20, 1 );
	}

	/**
	 * Register author purger.
	 *
	 * @return void
	 */
	private function register_author_purger(): void {
		add_action( 'solidwp/performance/cache/purge/post/queued', $this->container->callback( Author_Purger::class, 'on_post_purge' ), 10, 1 );
		add_action( 'deleted_user', $this->container->callback( Author_Purger::class, 'on_user_change' ), 20, 0 );
		add_action( 'profile_update', $this->container->callback( Author_Purger::class, 'on_user_change' ), 20, 0 );
	}

	/**
	 * Register term purger.
	 *
	 * @return void
	 */
	private function register_term_purger(): void {
		/**
		 * Posts assigned more than the threshold will trigger a full cache purge.
		 *
		 * @param int $max_term_count The maximum number of terms a post can have.
		 */
		$max_term_count = (int) apply_filters( 'solidwp/performance/cache/purge/max_term_count', 1500 );

		$this->container->when( Term_Purger::class )
						->needs( '$max_term_count' )
						->give( static fn(): int => $max_term_count );

		$this->container->singleton( Term_Purger::class, Term_Purger::class );
		add_action( 'solidwp/performance/cache/purge/post/queued', $this->container->callback( Term_Purger::class, 'on_post_purge' ), 20, 1 );
		add_action(
			'set_object_terms',
			function ( $object_id, $terms, $term_ids, $taxonomy, $append, $old_term_ids ): void {
				$this->container->get( Term_Purger::class )->on_term_set( (int) $object_id, (array) $term_ids, (array) $old_term_ids, (string) $taxonomy );
			},
			20,
			6
		);

		add_action(
			'pre_delete_term',
			function ( $term_id, $taxonomy ): void {
				$this->container->get( Term_Purger::class )->on_term_change( $taxonomy );
			},
			1,
			2
		);

		add_action(
			'edit_terms',
			function ( $term_id, $taxonomy ): void {
				$this->container->get( Term_Purger::class )->on_term_change( $taxonomy );
			},
			1,
			2
		);

		add_action(
			'saved_term',
			function ( $term_id, $tt_id, $taxonomy ): void {
				$this->container->get( Term_Purger::class )->on_term_change( $taxonomy );
			},
			1,
			3
		);
	}

	/**
	 * Register archive purger.
	 *
	 * @return void
	 */
	private function register_archive_purger(): void {
		add_action( 'solidwp/performance/cache/purge/post/queued', $this->container->callback( Archive_Purger::class, 'purge_date_archive' ), 10, 1 );
		add_action( 'solidwp/performance/cache/purge/post/queued', $this->container->callback( Archive_Purger::class, 'purge_post_type_archive' ), 10, 1 );
	}

	/**
	 * Register home purger.
	 *
	 * @return void
	 */
	private function register_home_purger(): void {
		add_action( 'solidwp/performance/cache/purge/post/queued', $this->container->callback( Home_Purger::class, 'on_post_purge' ), 15, 0 );
	}

	/**
	 * Configure the Option Purger with the different WP option names can trigger a cache flush.
	 *
	 * @return void
	 */
	private function register_option_purger(): void {
		$this->container->singleton( Option_Purger::class, Option_Purger::class );
		$this->container->when( Option_Purger::class )
						->needs( '$option_names' )
						->give(
						// Different WP option names that will force a cache flush.
							static fn(): array => array_fill_keys(
								[
									// options-general.php.
									'blogname',
									'blogdescription',
									'site_icon',
									'WPLANG',
									'timezone_string',
									'gmt_offset',
									'date_format',
									'time_format',
									'start_of_week',

									// options-reading.php.
									'page_for_posts',
									'page_on_front',
									'posts_per_page',
									'blog_public',

									// options-discussion.php.
									'require_name_email',
									'comment_registration',
									'close_comments_for_old_posts',
									'show_comments_cookies_opt_in',
									'thread_comments',
									'thread_comments_depth',
									'page_comments',
									'comments_per_page',
									'default_comments_page',
									'comment_order',
									'show_avatars',
									'avatar_rating',
									'avatar_default',

									// options-permalink.php.
									'permalink_structure',
									'category_base',
									'tag_base',

									// Appearance: themes.php.
									'template',
									'stylesheet',
								],
								true
							)
						);

		add_action( 'updated_option', $this->container->callback( Option_Purger::class, 'on_option_change' ), 10, 1 );
	}

	/**
	 * Register template purger.
	 *
	 * @return void
	 */
	private function register_template_purger(): void {
		$this->container->singleton( Template_Purger::class, Template_Purger::class );
		$this->container->when( Template_Purger::class )
			->needs( '$post_types' )
			->give(
				// Internal post types when changed, require a full cache purge.
				static fn(): array => [
					// Gutenberg.
					'wp_block',
					'wp_navigation',
					'wp_template',
					'wp_template_part',
					'wp_global_styles',
				]
			);

		add_action( 'delete_post', $this->container->callback( Template_Purger::class, 'on_delete' ), 9, 1 );
		add_action( 'wp_trash_post', $this->container->callback( Template_Purger::class, 'on_delete' ), 9, 1 );
		add_action( 'transition_post_status', $this->container->callback( Template_Purger::class, 'on_transition' ), 9, 3 );
	}

	/**
	 * Register the legacy menu purger.
	 *
	 * @return void
	 */
	private function register_menu_purger(): void {
		add_action( 'wp_update_nav_menu', $this->container->callback( Menu_Purger::class, 'on_menu_update' ), 20, 0 );
	}
}
