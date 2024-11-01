<?php
/**
 * Application/Plugin specific functions.
 */

declare( strict_types=1 );

use SolidWP\Performance\Container;
use SolidWP\Performance\Core;
use SolidWP\Performance\lucatume\DI52\Container as DI52Container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the plugin singleton and ensure we only have a single
 * instance of our container.
 *
 * @return Core
 */
function swpsp_plugin(): Core {
	// In the event we don't have a container in our core class yet.
	$container = new Container( new DI52Container() );

	// This singleton will not use a new container if one is already set.
	return Core::instance( realpath( __DIR__ . '/../../solid-performance.php' ), $container );
}

/**
 * Log to the error log if WP_DEBUG is set to true.
 *
 * @param string|mixed[] $data The data to log.
 *
 * @return void
 */
function swpsp_log( $data ): void {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! function_exists( 'error_log' ) ) {
		return;
	}

	if ( is_array( $data ) ) {
		$data = print_r( $data, true );
	}

	error_log( $data );
}
