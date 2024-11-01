<?php
/**
 * Handles fetching assets like images.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Assets;

/**
 * Handles fetching assets like images.
 *
 * @since 1.0.0
 *
 * @package SolidWP\Performance
 */
final class Asset {

	/**
	 * The URL to the plugin's main folder.
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * The server path to the plugin's main folder.
	 *
	 * @var string
	 */
	private string $plugin_dir;

	/**
	 * @param  string $plugin_url The URL to the plugin's main folder.
	 * @param  string $plugin_dir The server path to the plugin's main folder.
	 */
	public function __construct( string $plugin_url, string $plugin_dir ) {
		$this->plugin_url = $plugin_url;
		$this->plugin_dir = $plugin_dir;
	}

	/**
	 * Get an asset URL.
	 *
	 * @param  string $path A relative path to an asset.
	 *
	 * @return string The full URL to the asset.
	 */
	public function get_url( string $path = '' ): string {
		return $this->plugin_url . $path;
	}

	/**
	 * Get an asset directory path.
	 *
	 * @param  string $path A relative path to an asset.
	 *
	 * @return string
	 */
	public function get_dir( string $path = '' ): string {
		return $this->plugin_dir . $path;
	}
}
