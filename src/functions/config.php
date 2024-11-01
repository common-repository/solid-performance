<?php
/**
 * Global functions that provide access to the plugin configuration.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

use SolidWP\Performance\Config\Config;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the config that is created from a set of defaults, site settings, and network settings.
 *
 * @since 0.1.0
 *
 * @param string $key The key to retrieve from the config in dot notation.
 *
 * @return mixed
 */
function swpsp_config_get( string $key ) {
	// In versions < 1.1.0, advanced-cache.php does not have the app.php require and fatal errors.
	if ( ! function_exists( 'swpsp_plugin' ) ) {
		require_once __DIR__ . '/app.php';
	}

	return swpsp_plugin()->container()->get( Config::class )->get( $key );
}
