<?php
/**
 * Global functions related to the filesystem.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides an instance of WordPress' native direct filesystem.
 *
 * @since 0.1.0
 *
 * @return WP_Filesystem_Direct
 */
function swpsp_direct_filesystem() {
	if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	}

	if ( ! defined( 'FS_CHMOD_FILE' ) ) {
		define( 'FS_CHMOD_FILE', swpsp_get_file_mode() );
	}

	if ( ! defined( 'FS_CHMOD_DIR' ) ) {
		define( 'FS_CHMOD_DIR', swpsp_get_dir_mode() );
	}

	return new WP_Filesystem_Direct( [] );
}

/**
 * Gets the default file mode as an octal integer.
 *
 * @since 0.1.0
 *
 * @return int
 */
function swpsp_get_file_mode() {
	if ( defined( 'FS_CHMOD_FILE' ) ) {
		return FS_CHMOD_FILE;
	}

	return ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 );
}

/**
 * Gets the default file mode as an octal integer.
 *
 * @since 0.1.0
 *
 * @return int
 */
function swpsp_get_dir_mode() {
	if ( defined( 'FS_CHMOD_DIR' ) ) {
		return FS_CHMOD_DIR;
	}

	return ( fileperms( ABSPATH ) & 0777 | 0755 );
}
