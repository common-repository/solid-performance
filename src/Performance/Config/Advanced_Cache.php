<?php
/**
 * All functionality related to creating and modifying the advanced-cache.php drop-in.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Config;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle all functionality related to the advanced-cache.php drop-in.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Advanced_Cache {

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
	 * @param  string $version The plugin's current version.
	 * @param  string $destination The server path to advanced-cache.php.
	 */
	public function __construct( string $version, string $destination ) {
		$this->version     = $version;
		$this->destination = $destination;
	}


	/**
	 * Generates a copy of an advanced-cache.php template and adds it to the wp-content directory.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $template  Full path to a template used when generating the drop-in.
	 *
	 * @return bool
	 */
	public function generate( string $template = '' ): bool {
		$filesystem = swpsp_direct_filesystem();

		$template = $template ?: dirname( SWPSP_PLUGIN_FILE ) . '/src/views/cache/advanced-cache.php';

		// The plugin directory relative to WP_CONTENT_DIR.
		$plugin_dir = basename( WP_PLUGIN_DIR ) . '/' . basename( dirname( SWPSP_PLUGIN_FILE ) );

		$advanced_cache_content = $filesystem->get_contents( $template );

		$advanced_cache_content = preg_replace( '/{{swpsp-cache-dir}}/', swpsp_config_get( 'page_cache.cache_dir' ), $advanced_cache_content );
		$advanced_cache_content = preg_replace( '/{{swpsp-plugin-dir}}/', $plugin_dir, $advanced_cache_content );
		$advanced_cache_content = preg_replace( '/{{swpsp-advanced-cache-version}}/', $this->version, $advanced_cache_content );

		return $filesystem->put_contents( $this->destination, $advanced_cache_content, swpsp_get_file_mode() );
	}

	/**
	 * Whether an advanced-cache.php file exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return is_file( $this->destination );
	}

	/**
	 * Removes the advanced-cache drop-in.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function remove(): bool {
		// There is no file to remove, just return successfully.
		if ( ! $this->exists() ) {
			return true;
		}

		return swpsp_direct_filesystem()->delete( $this->destination );
	}
}
