<?php
/**
 * Handles all WP-CLI commands.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\WP_CLI;

use SolidWP\Performance\Page_Cache;
use WP_CLI_Command;
use WP_CLI;

/**
 * Manages Solid Performance functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Performance extends WP_CLI_Command {

	/**
	 * @var Page_Cache
	 */
	private Page_Cache $page_cache;

	/**
	 * @param  Page_Cache $page_cache The page cache object.
	 */
	public function __construct( Page_Cache $page_cache ) {
		$this->page_cache = $page_cache;

		parent::__construct();
	}

	/**
	 * Clears all pages stored in the page cache.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear all cached pages.
	 *     $ wp solid perf clear
	 *     Success: Page cache has been cleared
	 *
	 * @since 0.1.0
	 */
	public function clear() {
		$result = $this->page_cache->clear();

		if ( ! $result ) {
			WP_CLI::error( 'Unable to clear cached pages.' );
		}

		WP_CLI::success( 'Page cache has been cleared' );
	}

	/**
	 * Controls the debug setting.
	 *
	 * Displays the current status of the debug setting for Solid Performance when used without any parameters. Pass `enable` or `disable` to toggle the setting on/off.
	 *
	 * ## OPTIONS
	 *
	 * [<enable|disable>]
	 * : Enable or disable the debug option.
	 *
	 * ## EXAMPLES
	 *
	 *     # Get the current status of the debug option.
	 *     $ wp solid perf debug
	 *     Success: Debug setting is currently <enabled|disabled>.
	 *
	 *     # Enable the debug option for page caching.
	 *     $ wp solid perf debug enable
	 *     Success: Debug setting enabled.
	 *
	 *     # Disable the debug option for page caching.
	 *     $ wp solid perf debug disable
	 *     Success: Debug setting disabled.
	 *
	 * @param array $args An array of positional arguments for the command.
	 */
	public function debug( array $args = [] ): void {
		$status = $this->page_cache->is_debug_on() ? 'enabled' : 'disabled';

		if ( ! empty( $args[0] ) ) {
			$result = $this->page_cache->debug( $args[0] === 'enable' );
			$status = $result ? 'enabled' : 'disabled';
			WP_CLI::line( 'Updating debug mode...' );
		}

		WP_CLI::Success( sprintf( 'Debug mode %s', $status ) );
	}

	/**
	 * Enables the page cache.
	 *
	 * ## EXAMPLES
	 *
	 *     # Enable the page cache
	 *     $ wp solid perf on
	 *     Success: Page cache enabled
	 */
	public function on() {
		$status = $this->page_cache->on();

		if ( ! $status ) {
			WP_CLI::error( 'Unable to enable page cache' );
		}

		WP_CLI::success( 'Page cache enabled' );
	}

	/**
	 * Disables the page cache.
	 *
	 * ## EXAMPLES
	 *
	 *     # Disable the page cache
	 *     $ wp solid perf off
	 *     Success: Page cache disabled
	 */
	public function off() {
		$status = $this->page_cache->off();

		if ( ! $status ) {
			WP_CLI::error( 'Unable to disable page cache' );
		}

		WP_CLI::success( 'Page cache disabled' );
	}

	/**
	 * Gets the current status of page caching.
	 *
	 * ## EXAMPLES
	 *
	 *     # Get the current status of page caching.
	 *     $ wp solid perf status
	 *     Success: Page cache <enabled|disabled>
	 */
	public function status() {
		$status = $this->page_cache->is_on() ? WP_CLI::colorize( '%genabled%n' ) : WP_CLI::colorize( '%rdisabled%n' );

		WP_CLI::line( sprintf( 'Page cache is: %s', $status ) );
	}

	/**
	 * Regenerates the advanced-cache.php file.
	 *
	 * ## EXAMPLES
	 *
	 *     # Regenerates the advanced-cache.php file.
	 *     $ wp solid perf regenerate
	 *     Success: advanced-cache.php regenerated
	 */
	public function regenerate() {
		$result = $this->page_cache->regenerate_advanced_cache();

		if ( ! $result ) {
			WP_CLI::error( 'Unable to regenerate the advanced-cache.php file' );
		}

		WP_CLI::success( 'advanced-cache.php file regenerated' );
	}
}
