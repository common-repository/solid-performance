<?php
/**
 * Handles purging the entire cache when global templates change.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use WP_Post;

/**
 * Handles purging the entire cache when global templates change.
 *
 * @package SolidWP\Performance
 */
final class Template_Purger {

	/**
	 * The different post types that would trigger a cache purge.
	 *
	 * @var string[]
	 */
	private array $post_types;

	/**
	 * @var Batch_Purger
	 */
	private Batch_Purger $batch_purger;

	/**
	 * @param  string[]     $post_types  The different post types that would trigger a cache purge.
	 * @param  Batch_Purger $batch_purger The batch purger.
	 */
	public function __construct( array $post_types, Batch_Purger $batch_purger ) {
		$this->post_types   = $post_types;
		$this->batch_purger = $batch_purger;
	}

	/**
	 * Get the post types which when updated, will trigger a full cache purge.
	 *
	 * @return string[]
	 */
	public function post_types(): array {
		return $this->post_types;
	}

	/**
	 * Purge when one of the post type is trashed or deleted.
	 *
	 * @action delete_post
	 * @action wp_trash_post
	 *
	 * @param  int $post_id The Post ID.
	 *
	 * @return void
	 */
	public function on_delete( int $post_id ): void {
		$this->purge( $post_id );
	}

	/**
	 * Trigger a cache purge if one of the post types changed.
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

		$this->purge( $post->ID );
	}

	/**
	 * Whether this post type will allow a full cache purge.
	 *
	 * @param  int $post_id The Post ID.
	 *
	 * @return bool
	 */
	private function is_allowed_post_type( int $post_id ): bool {
		$post_type = get_post_type( $post_id );

		return $post_type && in_array( $post_type, $this->post_types, true );
	}

	/**
	 * Queue up for a full page cache purge.
	 *
	 * @param  int $post_id The Post ID.
	 *
	 * @return void
	 */
	private function purge( int $post_id ): void {
		if ( ! $this->is_allowed_post_type( $post_id ) ) {
			return;
		}

		$this->batch_purger->queue_purge_all();
	}
}
