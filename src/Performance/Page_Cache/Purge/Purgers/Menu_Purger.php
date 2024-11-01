<?php
/**
 * Handles legacy menu purging for non-block themes.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;

/**
 * Handles legacy menu purging for non-block themes.
 *
 * @package SolidWP\Performance
 */
final class Menu_Purger {

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
	 * Purge the entire cache when a menu is saved.
	 *
	 * @action wp_update_nav_menu
	 *
	 * @return void
	 */
	public function on_menu_update(): void {
		$this->batch_purger->queue_purge_all();
	}
}
