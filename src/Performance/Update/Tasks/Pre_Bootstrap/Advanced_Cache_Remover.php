<?php
/**
 * Deletes our advanced-cache.php file if its version is out of date.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Update\Tasks\Pre_Bootstrap;

use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Flintstone\Flintstone;
use SolidWP\Performance\Update\Tasks\Contracts\Task;
use SolidWP\Performance\Update\Tasks\Post_Bootstrap\Advanced_Cache_Restorer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deletes our advanced-cache.php file if its version is out of date.
 *
 * @package SolidWP\Performance
 */
final class Advanced_Cache_Remover implements Task {

	public const KEY = 'update_advanced_cache';

	/**
	 * The plugin's current version.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * The server path to the advanced-cache.php file.
	 *
	 * @var string
	 */
	private string $destination;

	/**
	 * The flat file key/value store db.
	 *
	 * @var Flintstone
	 */
	private Flintstone $db;

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @param  string     $version      The plugin's current version.
	 * @param  string     $destination  The server path to the advanced-cache.php file.
	 * @param  Flintstone $db           The flat file key/value store db.
	 * @param  Config     $config The config object.
	 */
	public function __construct( string $version, string $destination, Flintstone $db, Config $config ) {
		$this->version     = $version;
		$this->destination = $destination;
		$this->db          = $db;
		$this->config      = $config;
	}

	/**
	 * If our advanced-cache.php file is in place, we'll have a version defined with
	 * SWPSP_ADVANCED_CACHE_VERSION.
	 *
	 * If that version is lower than the current version of our plugin, we'll execute
	 * this task.
	 *
	 * @return bool
	 */
	public function should_run(): bool {
		if ( ! defined( 'SWPSP_ADVANCED_CACHE_VERSION' ) ) {
			return false;
		}

		$existing_version = SWPSP_ADVANCED_CACHE_VERSION;

		return version_compare( $this->version, $existing_version, '>' );
	}

	/**
	 * Remove the advanced-cache.php file and stop execution.
	 *
	 * @see Advanced_Cache_Restorer::should_run()
	 *
	 * @return void
	 */
	public function run(): void {
		// Signal to the Advanced Cache Restorer that it will need to recreate this file.
		$this->db->set( self::KEY, true );

		// Set the in-memory config to disable the cache to stop execution inside advanced-cache.php.
		$this->config->set( 'page_cache.enabled', false );

		// Delete advanced-cache.php.
		unlink( $this->destination );
	}
}
