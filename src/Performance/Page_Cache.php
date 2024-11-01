<?php
/**
 * A set of methods that let other developers interact with Solid Performance.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance;

use SolidWP\Performance\Config\Advanced_Cache;
use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Config\WP_Config;
use SolidWP\Performance\Page_Cache\Purge\Purge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A set of methods that let other developers interact with Solid Performance.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Page_Cache {

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @var WP_Config
	 */
	private WP_Config $wp_config;

	/**
	 * @var Purge
	 */
	private Purge $purge;

	/**
	 * @var Advanced_Cache
	 */
	private Advanced_Cache $advanced_cache;

	/**
	 * @param  Config         $config The config class.
	 * @param  WP_Config      $wp_config The wp config class.
	 * @param  Purge          $purge The cache purger.
	 * @param  Advanced_Cache $advanced_cache The advanced cache?.
	 */
	public function __construct(
		Config $config,
		WP_Config $wp_config,
		Purge $purge,
		Advanced_Cache $advanced_cache
	) {
		$this->config         = $config;
		$this->wp_config      = $wp_config;
		$this->purge          = $purge;
		$this->advanced_cache = $advanced_cache;
	}

	/**
	 * Enables the page cache.
	 *
	 * @since 0.1.0
	 *
	 * @return bool true on success, false on failure
	 */
	public function on(): bool {
		$this->config->set( 'page_cache.enabled', true )->queue();
		$this->wp_config->set_wp_cache( 'true' );

		return true;
	}

	/**
	 * Disables the page cache.
	 *
	 * @since 0.1.0
	 *
	 * @return bool true on success, false on failure
	 */
	public function off(): bool {
		$this->config->set( 'page_cache.enabled', false )->queue();
		$this->wp_config->set_wp_cache( 'false' );

		return true;
	}

	/**
	 * Checks if the page cache is currently enabled.
	 *
	 * @since 0.1.0
	 *
	 * @return bool true when enabled, false when disabled.
	 */
	public function is_on(): bool {
		return $this->wp_config->get_wp_cache_status();
	}

	/**
	 * Clears all pages from the page cache.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function clear(): bool {
		return $this->purge->all_pages();
	}

	/**
	 * Regenerates the advanced-cache.php file.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function regenerate_advanced_cache(): bool {
		return $this->advanced_cache->generate();
	}

	/**
	 * Gets the current status of debug mode.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_debug_on(): bool {
		return $this->config->get( 'page_cache.debug' );
	}

	/**
	 * Sets the state of debug mode.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $state The state for debug mode.
	 *
	 * @return bool
	 */
	public function debug( bool $state ): bool {
		$this->config->set( 'page_cache.debug', $state )->queue();

		return (bool) $this->config->get( 'page_cache.debug' );
	}
}
