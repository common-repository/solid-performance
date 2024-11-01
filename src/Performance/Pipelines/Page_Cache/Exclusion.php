<?php
/**
 * Prevent requests from being saved to the cache when matching user-defined exclusion patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP|Performance
 */

namespace SolidWP\Performance\Pipelines\Page_Cache;

use SolidWP\Performance\Config\Config;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent caching requests that match user-defined patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Exclusion extends Pattern {

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @param  Config $config The config class.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_patterns(): array {
		return $this->config->get( 'page_cache.exclusions' );
	}
}
