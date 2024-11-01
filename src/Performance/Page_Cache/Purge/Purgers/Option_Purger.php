<?php
/**
 * Handles purging the cache based on when different options change.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Purgers;

use SolidWP\Performance\Page_Cache\Purge\Purge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles purging the cache based on when different options change.
 *
 * @see \SolidWP\Performance\Page_Cache\Purge\Provider::register_option_purger()
 *
 * @package SolidWP\Performance
 */
final class Option_Purger {

	/**
	 * @var Home_Purger
	 */
	private Home_Purger $home_purger;

	/**
	 * @var Purge
	 */
	private Purge $purger;

	/**
	 * The names of the options that are allowed to purge the cache.
	 *
	 * @var array<string, bool>
	 */
	private array $allowed_options;

	/**
	 * @param  Home_Purger         $home_purger   The home purger.
	 * @param  Purge               $purger        The purger.
	 * @param  array<string, bool> $option_names  The options that allow purging.
	 */
	public function __construct(
		Home_Purger $home_purger,
		Purge $purger,
		array $option_names
	) {
		$this->home_purger     = $home_purger;
		$this->purger          = $purger;
		$this->allowed_options = $option_names;
	}

	/**
	 * Purge the entire cache or the homepage cache based on which settings
	 * options changed.
	 *
	 * @action update_option
	 *
	 * @param  string $option  Name of the option to update.
	 *
	 * @return void
	 */
	public function on_option_change( string $option ): void {
		if ( ! isset( $this->allowed_options[ $option ] ) ) {
			return;
		}

		// If homepage options changed, purge just the home page and page for posts.
		if ( $option === 'page_for_posts' || $option === 'page_on_front' ) {
			$this->home_purger->on_post_purge();

			return;
		}

		// Otherwise, purge the entire cache.
		$this->purger->all_pages();
	}
}
