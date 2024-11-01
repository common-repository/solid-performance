<?php
/**
 * Updates our advanced-cache.php with the latest version if it was
 * deleted by another updater task.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Update\Tasks\Post_Bootstrap;

use SolidWP\Performance\Config\Advanced_Cache;
use SolidWP\Performance\Flintstone\Flintstone;
use SolidWP\Performance\Update\Tasks\Contracts\Task;
use SolidWP\Performance\Update\Tasks\Pre_Bootstrap\Advanced_Cache_Remover;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Updates our advanced-cache.php with the latest version if it was
 * deleted by another updater task.
 *
 * @see Advanced_Cache_Remover
 *
 * @package SolidWP\Performance
 */
final class Advanced_Cache_Restorer implements Task {

	/**
	 * The flat file key/value store db.
	 *
	 * @var Flintstone
	 */
	private Flintstone $db;

	/**
	 * @var Advanced_Cache
	 */
	private Advanced_Cache $advanced_cache;

	/**
	 * @param  Flintstone     $db The flat file key/value store db.
	 * @param  Advanced_Cache $advanced_cache The advanced cache object.
	 */
	public function __construct( Flintstone $db, Advanced_Cache $advanced_cache ) {
		$this->db             = $db;
		$this->advanced_cache = $advanced_cache;
	}

	/**
	 * If a previous task added the `update_advanced_cache` item.
	 *
	 * @return bool
	 */
	public function should_run(): bool {
		return (bool) $this->db->get( Advanced_Cache_Remover::KEY );
	}

	/**
	 * Regenerate the advanced-cache.php file.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->advanced_cache->generate();
	}
}
