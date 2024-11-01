<?php
/**
 * Handles all functionality for file expiration.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Page_Cache;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Cache file expiration.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Expiration {

	/**
	 * The default expiration length (1 Day in seconds).
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	private int $length;

	/**
	 * The class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param  int $seconds The default expiration length (1 Day in seconds).
	 *
	 * @throws InvalidArgumentException If the length is less than or equal to 0.
	 */
	public function __construct( int $seconds ) {
		if ( $seconds <= 0 ) {
			throw new InvalidArgumentException( 'The expiration $seconds must be greater than 0' );
		}

		$this->length = $seconds;
	}

	/**
	 * Determines if a file is older than the configured expiration.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file_path The full path to the file.
	 *
	 * @return bool
	 */
	public function file_expired( string $file_path ): bool {
		$age        = filemtime( $file_path );
		$expiration = $age + $this->length;

		return time() > $expiration;
	}

	/**
	 * Sets how long files are valid before they expire.
	 *
	 * @since 0.1.0
	 *
	 * @param int $seconds Seconds files are valid before they expire.
	 *
	 * @throws InvalidArgumentException If the length is less than or equal to 0.
	 *
	 * @return void
	 */
	public function set_expiration_length( int $seconds ): void {
		if ( $seconds <= 0 ) {
			throw new InvalidArgumentException( 'The expiration $seconds must be greater than 0' );
		}

		$this->length = $seconds;
	}

	/**
	 * Gets the number of seconds files should be kept before they expire.
	 *
	 * 0.1.0
	 *
	 * @return int
	 */
	public function get_expiration_length(): int {
		return $this->length;
	}
}
