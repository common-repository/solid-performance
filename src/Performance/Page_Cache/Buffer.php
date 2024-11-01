<?php
/**
 * The class responsible for collecting and storing fully rendered pages.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Page_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A class for collecting and storing rendered pages.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Buffer {

	/**
	 * An instance of Cache.
	 *
	 * @since 0.1.0
	 *
	 * @var Cache
	 */
	private Cache $cache;

	/**
	 * The class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Cache $cache An instance of Page_Cache\Cache.
	 */
	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Registers the callback with Output Buffering.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register(): void {
		ob_start( [ $this, 'handle' ] );
	}

	/**
	 * The callback registered in the Output Buffer to save response.
	 *
	 * @since 0.1.0
	 *
	 * @param string $buffer The output created for the request.
	 *
	 * @see https://www.php.net/manual/en/function.ob-start.php
	 *
	 * @return string
	 */
	public function handle( string $buffer ): string {
		if ( is_admin() ) {
			return $buffer;
		}

		$this->cache->save_output( $buffer );

		return $buffer;
	}
}
