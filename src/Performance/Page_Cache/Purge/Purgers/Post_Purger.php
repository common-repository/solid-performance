<?php
/**
 * Handles purging posts from the cache during different events.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;
use SolidWP\Performance\Page_Cache\Purge\Traits\With_Permalink;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles purging posts from the cache during different events.
 *
 * @see Provider::register_post_purger()
 *
 * @package SolidWP\Performance
 */
final class Post_Purger {

	use With_Permalink;

	/**
	 * A memoization cache that is indexed by post_id and contains
	 * the post permalink.
	 *
	 * @var array<int, string>
	 */
	private array $posts = [];

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
	 * Depending on a post's state at the time get_permalink() is called, it may return a pretty
	 * permalink or a non-pretty permalink, e.g. /?p=123 or /?page_id=123 etc...
	 *
	 * We'll attempt to capture the original pretty permalink here, however if it could still
	 * be non-pretty.
	 *
	 * @action pre_post_update
	 *
	 * @param  int             $post_id The Post ID.
	 * @param  mixed[]|WP_Post $post The Post Array or Post Object.
	 *
	 * @return void
	 */
	public function capture( int $post_id, $post = [] ): void {
		// No need to capture non-public posts.
		if ( ! is_post_publicly_viewable( $post_id ) ) {
			return;
		}

		// If this post is being trashed, we already captured the correct permalink.
		if ( isset( $post['post_status'] ) && $post['post_status'] === 'trash' ) {
			return;
		}

		$this->posts[ $post_id ] = get_permalink( $post_id );
	}

	/**
	 * Capture the full permalink before the post status is changed to trashed, otherwise
	 * we can never get the original URL.
	 *
	 * @filter pre_trash_post
	 *
	 * @param  bool|null $trash Whether to short-circuit trashing.
	 * @param  WP_Post   $post The post.
	 * @param  string    $previous_status The previous post status.
	 *
	 * @return bool|null
	 */
	public function capture_trashed( $trash, WP_Post $post, string $previous_status ) {
		if ( $previous_status === 'publish' ) {
			$this->posts[ $post->ID ] = get_permalink( $post );
		}

		return $trash;
	}

	/**
	 * Determine if we are going to try to purge the cached file.
	 *
	 * As this runs later during the request cycle, we should already have
	 * captured a permalink, although it could still not be the "pretty" version we need.
	 *
	 * @action transition_post_status
	 *
	 * @param  string  $new_status The new post status.
	 * @param  string  $old_status The old post status.
	 * @param  WP_Post $post The post.
	 *
	 * @return void
	 */
	public function on_transition( string $new_status, string $old_status, WP_Post $post ): void {
		// New post being published, should be nothing purge.
		if ( $old_status === 'auto-draft' && $new_status === 'publish' ) {
			return;
		}

		// If this post wasn't already published, should be nothing to purge.
		if ( $old_status !== 'publish' && $new_status !== $old_status ) {
			return;
		}

		$permalink = $this->posts[ $post->ID ] ?? '';

		// We captured the permalink earlier, where WordPress gave us a ?p=ID type permalink.
		if ( ! $this->is_pretty( $permalink ) ) {
			// The permalink should now be valid at this point in the request.
			$permalink = get_permalink( $post );
		}

		if ( ! $permalink ) {
			return;
		}

		$this->queue( $permalink, $post->ID );
	}

	/**
	 * Purge a post when it's deleted, in case trashing posts is disabled.
	 *
	 * @action before_delete_post
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function on_delete( int $post_id ): void {
		$permalink = get_permalink( $post_id );

		// If this isn't a pretty permalink, the post would have already been purged when trashed.
		if ( ! $this->is_pretty( $permalink ) ) {
			return;
		}

		$this->queue( $permalink, $post_id );
	}

	/**
	 * Purge the post when a new comment is added.
	 *
	 * @action wp_update_comment_count
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function on_comment_count( int $post_id ): void {
		$permalink = get_permalink( $post_id );

		// If this isn't a pretty permalink, the post would have already been purged when trashed.
		if ( ! $this->is_pretty( $permalink ) ) {
			return;
		}

		$this->queue( $permalink, $post_id, Purge_Strategy::PAGE_WITH_PAGINATION );
	}

	/**
	 * Queue the permalink to be purged.
	 *
	 * @param  string $permalink The permalink to purge.
	 * @param  int    $post_id The associated post ID.
	 * @param  string $strategy The purge strategy to use.
	 *
	 * @return void
	 */
	private function queue( string $permalink, int $post_id, string $strategy = Purge_Strategy::PAGE ): void {
		$this->batch->queue(
			Permalink::from(
				[
					'permalink'      => $permalink,
					'object_id'      => $post_id,
					'purge_strategy' => $strategy,
				]
			)
		);

		$this->queue_parent_posts( $post_id );

		unset( $this->posts[ $post_id ] );

		/**
		 * Fire off event other purgers can use when this post is queued to be purged.
		 *
		 * @param int $post_id The post ID that will be purged.
		 */
		do_action( 'solidwp/performance/cache/purge/post/queued', $post_id );
	}

	/**
	 * Queue a post's parents to be purged as well.
	 *
	 * Parent pages may often reference their child pages in them.
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	private function queue_parent_posts( int $post_id ): void {
		$parent_ids = get_post_ancestors( $post_id );

		foreach ( $parent_ids as $parent_id ) {
			$permalink = get_permalink( $parent_id );

			if ( ! $permalink || ! $this->is_pretty( $permalink ) ) {
				continue;
			}

			$this->batch->queue(
				Permalink::from(
					[
						'permalink'      => $permalink,
						'object_id'      => $post_id,
						'purge_strategy' => Purge_Strategy::PAGE,
					]
				)
			);

		}
	}
}
