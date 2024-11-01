<?php
/**
 * Captures term/taxonomy related URLs and purges them on the
 * shutdown action.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;
use SolidWP\Performance\Page_Cache\Purge\Traits\With_Permalink;
use WP_Error;
use WP_Term;

/**
 * Captures term/taxonomy related URLs and purges them on the
 * shutdown action.
 *
 * @package SolidWP\Performance
 */
final class Term_Purger {

	use With_Permalink;

	/**
	 * @var Batch_Purger
	 */
	private Batch_Purger $batch;

	/**
	 * The max term count a post can have before we purge the entire cache.
	 *
	 * @var int
	 */
	private int $max_term_count;

	/**
	 * @param  Batch_Purger $batch  The batch purger.
	 * @param  int          $max_term_count The max term count a post can have before we purge the entire cache.
	 */
	public function __construct( Batch_Purger $batch, int $max_term_count ) {
		$this->batch          = $batch;
		$this->max_term_count = $max_term_count;
	}

	/**
	 * Collect the taxonomy term URLs when a post is purged.
	 *
	 * @action solidwp/performance/cache/purge/post/queued
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function on_post_purge( int $post_id ): void {
		// Skip logic if we already have a full purge pending.
		if ( $this->batch->is_full_purge_pending() ) {
			return;
		}

		if ( ! is_post_publicly_viewable( $post_id ) ) {
			return;
		}

		$taxonomies = array_filter(
			get_taxonomies(),
			static fn( $taxonomy ) => is_taxonomy_viewable( $taxonomy )
		);

		// This is a reasonably fast query, even with lots of terms.
		$count = (int) wp_count_terms(
			[
				'taxonomy'   => $taxonomies,
				'object_ids' => $post_id,
				'hide_empty' => true,
			]
		);

		// Purge the entire cache if we have too many terms to purge.
		if ( $count > $this->max_term_count ) {
			$this->batch->queue_purge_all();

			return;
		}

		$terms = wp_get_post_terms( $post_id, $taxonomies );

		if ( ! is_array( $terms ) ) {
			return;
		}

		/** @var WP_Term $term */
		foreach ( $terms as $term ) {
			$permalink = get_term_link( $term );

			if ( ! $this->is_valid_permalink( $permalink ) ) {
				continue;
			}

			$this->batch->queue(
				Permalink::from(
					[
						'permalink'      => $permalink,
						'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
					]
				)
			);

			$this->capture_parents( $term->term_id, $term->taxonomy );
		}
	}

	/**
	 * Collect all taxonomy term URLs when the terms are set, to ensure we also
	 * flush taxonomy archives where this post was removed from.
	 *
	 * @action set_object_terms
	 *
	 * @param  int      $post_id       The post ID.
	 * @param  string[] $term_ids      An array of term taxonomy IDs.
	 * @param  int[]    $old_term_ids  Old array of term taxonomy IDs.
	 * @param  string   $taxonomy      The taxonomy associated with the terms.
	 *
	 * @return void
	 */
	public function on_term_set( int $post_id, array $term_ids, array $old_term_ids, string $taxonomy ): void {
		// Skip logic if we already have a full purge pending.
		if ( $this->batch->is_full_purge_pending() ) {
			return;
		}

		if ( ! is_post_publicly_viewable( $post_id ) || ! is_taxonomy_viewable( $taxonomy ) ) {
			return;
		}

		$ids = array_unique( array_map( 'intval', array_merge( $term_ids, $old_term_ids ) ) );

		// Purge the entire cache if we have too many terms to purge.
		if ( count( $ids ) > $this->max_term_count ) {
			$this->batch->queue_purge_all();

			return;
		}

		foreach ( $ids as $id ) {
			$term = get_term( $id, $taxonomy );

			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$permalink = get_term_link( $term );

			if ( ! $this->is_valid_permalink( $permalink ) ) {
				continue;
			}

			$this->batch->queue(
				Permalink::from(
					[
						'permalink'      => $permalink,
						'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
					]
				)
			);

			$this->capture_parents( $id, $taxonomy );
		}
	}

	/**
	 * When a term is created/edited or deleted, flush the entire cache as it's
	 * going to be more efficient than querying for everything.
	 *
	 * @action pre_delete_term
	 * @action edit_terms
	 * @action saved_term
	 *
	 * @param  string $taxonomy The taxonomy slug.
	 *
	 * @return void
	 */
	public function on_term_change( string $taxonomy ): void {
		if ( ! is_taxonomy_viewable( $taxonomy ) ) {
			return;
		}

		$this->batch->queue_purge_all();
	}

	/**
	 * Return the configured max term count.
	 *
	 * @return int
	 */
	public function max_term_count(): int {
		return $this->max_term_count;
	}

	/**
	 * Capture the URLs for any parent taxonomy terms.
	 *
	 * @param  int    $term_id The Term ID.
	 * @param  string $taxonomy_name The taxonomy name.
	 *
	 * @return void
	 */
	private function capture_parents( int $term_id, string $taxonomy_name = '' ): void {
		if ( ! is_taxonomy_hierarchical( $taxonomy_name ) ) {
			return;
		}

		$parents = get_ancestors( $term_id, $taxonomy_name, 'taxonomy' );

		foreach ( $parents as $parent_id ) {
			$parent = get_term( $parent_id, $taxonomy_name );

			if ( ! $parent instanceof WP_Term ) {
				continue;
			}

			$permalink = get_term_link( $parent_id, $parent->taxonomy );

			if ( ! $this->is_valid_permalink( $permalink ) ) {
				continue;
			}

			$this->batch->queue(
				Permalink::from(
					[
						'permalink'      => $permalink,
						'purge_strategy' => Purge_Strategy::PAGE_WITH_PAGINATION,
					]
				)
			);
		}
	}

	/**
	 * Whether a Permalink is valid and is in "pretty" form.
	 *
	 * @param string|false|WP_Error $permalink  The Permalink URI.
	 *
	 * @return bool
	 */
	private function is_valid_permalink( $permalink ): bool {
		if ( ! $permalink || is_wp_error( $permalink ) ) {
			return false;
		}

		return $this->is_pretty( $permalink );
	}
}
