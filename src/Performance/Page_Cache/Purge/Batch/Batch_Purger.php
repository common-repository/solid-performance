<?php
/**
 * Queues up Permalink objects that will be batch purged on the
 * shutdown action.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Batch;

use Countable;
use SolidWP\Performance\Page_Cache\Purge\Purge;
use SolidWP\Performance\Page_Cache\Purge\Traits\With_Permalink;
use SolidWP\Performance\Shutdown\Contracts\Terminable;

/**
 * Queues up Permalink objects that will be batch purged on the
 * shutdown action.
 *
 * @see Permalink
 *
 * @package SolidWP\Performance
 */
final class Batch_Purger implements Terminable, Countable {

	use With_Permalink;

	/**
	 * @var array<string, Permalink>
	 */
	private array $items = [];

	/**
	 * Whether we're going to purge the entire cache.
	 *
	 * @var bool
	 */
	private bool $purge_all = false;

	/**
	 * @var Purge
	 */
	private Purge $purger;

	/**
	 * @param  Purge $purger  The purger.
	 */
	public function __construct( Purge $purger ) {
		$this->purger = $purger;
	}

	/**
	 * Queue a Permalink object to be purged.
	 *
	 * @param  Permalink $permalink The permalink object.
	 *
	 * @return void
	 */
	public function queue( Permalink $permalink ): void {
		// We're queued up to purge everything, save the memory.
		if ( $this->purge_all ) {
			return;
		}

		$this->items[ $permalink->hash() ] ??= $permalink;
	}

	/**
	 * Queue up to purge the entire cache.
	 *
	 * @return void
	 */
	public function queue_purge_all(): void {
		$this->purge_all = true;

		// No sense in keeping collected permalinks in memory.
		$this->clear_queue();
	}

	/**
	 * The number of permalinks in the hash set.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->items );
	}

	/**
	 * Whether our collected permalinks are empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return $this->count() <= 0;
	}

	/**
	 * Clear the queue of all permalinks.
	 *
	 * @return void
	 */
	public function clear_queue(): void {
		$this->items = [];
	}

	/**
	 * Reset the batch to the default state.
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->clear_queue();
		$this->purge_all = false;
	}

	/**
	 * Whether we are planning on doing a full purge.
	 *
	 * @return bool
	 */
	public function is_full_purge_pending(): bool {
		return $this->purge_all;
	}

	/**
	 * Purge captured permalinks on shutdown.
	 *
	 * @action shutdown
	 * @action solidwp/performance/terminate
	 *
	 * @return void
	 */
	public function terminate(): void {
		if ( $this->purge_all ) {
			$this->purger->all_pages();
			$this->reset();

			return;
		}

		if ( $this->is_empty() ) {
			return;
		}

		foreach ( $this->items as $item ) {
			$this->purger->by_permalink( $item );
		}

		// Reset as terminable middleware runs twice, we only need to run this once.
		$this->reset();
	}
}
