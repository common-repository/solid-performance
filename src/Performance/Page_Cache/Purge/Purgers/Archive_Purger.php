<?php
/**
 * Handles purging archives when a post changes state.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;
use SolidWP\Performance\Page_Cache\Purge\Traits\With_Permalink;

/**
 * Handles purging archives when a post changes state.
 *
 * @package SolidWP\Performance
 */
final class Archive_Purger {

	use With_Permalink;

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
	 * Purge the date archive where this post would be found.
	 *
	 * @action solidwp/performance/cache/purge/post/queued
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function purge_date_archive( int $post_id ): void {
		$date = get_post_datetime( $post_id );

		if ( ! $date ) {
			return;
		}

		$year  = $date->format( 'Y' );
		$month = $date->format( 'm' );
		$day   = $date->format( 'd' );

		$permalinks = [
			get_year_link( $year ),
			get_month_link( $year, $month ),
			get_day_link( $year, $month, $day ),
		];

		foreach ( $permalinks as $permalink ) {
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
	 * Purge a post type's archive when a post changes.
	 *
	 * @action solidwp/performance/cache/purge/post/queued
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	public function purge_post_type_archive( int $post_id ): void {
		$post_type = get_post_type( $post_id );

		// The "post" archive is handled via the Home Purger.
		if ( ! $post_type || $post_type === 'post' ) {
			return;
		}

		$permalink = get_post_type_archive_link( $post_type );

		if ( ! $permalink || ! $this->is_pretty( $permalink ) ) {
			return;
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
