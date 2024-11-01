<?php
/**
 * Handles purging author URL caches when a post changes.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;

/**
 * Handles purging author URL caches when a post changes.
 *
 * @package SolidWP\Performance
 */
final class Author_Purger {

	/**
	 * @var Batch_Purger
	 */
	private Batch_Purger $batch;

	/**
	 * @param  Batch_Purger $batch  The batch purger.
	 */
	public function __construct( Batch_Purger $batch ) {
		$this->batch = $batch;
	}

	/**
	 * Purge an author's archive and pagination by user ID.
	 *
	 * @param  int $user_id The user ID.
	 *
	 * @return void
	 */
	public function by_user_id( int $user_id ) {
		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return;
		}

		$permalink = get_author_posts_url( $user->ID, $user->user_nicename );

		$this->batch->queue(
			Permalink::from(
				[
					'permalink'      => $permalink,
					'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
				]
			)
		);
	}

	/**
	 * Purge the author's post URL if a post was changed.
	 *
	 * @action solidwp/performance/cache/purge/post/queued
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function on_post_purge( int $post_id ): void {
		$author_id = get_post_field( 'post_author', $post_id );

		if ( ! $author_id ) {
			return;
		}

		$this->by_user_id( (int) $author_id );
	}

	/**
	 * Purge the entire cache if a user's profile is updated or deleted.
	 *
	 * @action delete_user
	 * @action profile_update
	 *
	 * @note The full purge also handles reassigned author posts.
	 *
	 * @return void
	 */
	public function on_user_change(): void {
		$this->batch->queue_purge_all();
	}
}
