<?php
/**
 * Handles purging the home page when other posts are purged.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles purging the home page when other posts are purged.
 *
 * @package SolidWP\Performance
 */
final class Home_Purger {

	/**
	 * @var Batch_Purger
	 */
	private Batch_Purger $batch_purger;

	/**
	 * @param  Batch_Purger $batch_purger The batch purger.
	 */
	public function __construct( Batch_Purger $batch_purger ) {
		$this->batch_purger = $batch_purger;
	}

	/**
	 * Purge the home page and custom posts page when a post is purged.
	 *
	 * @action solidwp/performance/cache/purge/post/queued
	 *
	 * @return void
	 */
	public function on_post_purge(): void {
		$this->purge_home();
		$this->purge_posts_page();
	}

	/**
	 * Purge the home/front page.
	 *
	 * @return void
	 */
	private function purge_home(): void {
		$this->batch_purger->queue(
			Permalink::from(
				[
					'permalink'      => get_home_url(),
					'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
				]
			)
		);
	}

	/**
	 * Purge the custom posts page, if set.
	 *
	 * @return void
	 */
	private function purge_posts_page(): void {
		$post_id = (int) get_option( 'page_for_posts' );

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! is_post_publicly_viewable( $post ) ) {
			return;
		}

		$this->batch_purger->queue(
			Permalink::from(
				[
					'permalink'      => get_permalink( $post ),
					'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
				]
			)
		);
	}
}
