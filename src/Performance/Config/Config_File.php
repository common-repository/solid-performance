<?php
/**
 * Contains the location to the configuration file(s) on disk.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains the location to the configuration file(s) on disk.
 *
 * @package SolidWP\Performance
 */
final class Config_File {

	/**
	 * The server directory where the config file is stored.
	 *
	 * @var string
	 */
	private string $dir;

	/**
	 * @param  string $dir The server directory where the config file is stored.
	 *
	 * @throws InvalidArgumentException If an empty directory is provided.
	 */
	public function __construct( string $dir ) {
		if ( empty( $dir ) ) {
			throw new InvalidArgumentException( 'The config directory cannot be empty' );
		}

		$this->dir = $dir;
	}

	/**
	 * The server directory of the configuration file.
	 *
	 * @return string
	 */
	public function dir(): string {
		return $this->dir;
	}

	/**
	 * The full server path to the configuration file.
	 *
	 * @return string
	 */
	public function get(): string {
		return $this->dir . '/config.php';
	}
}
