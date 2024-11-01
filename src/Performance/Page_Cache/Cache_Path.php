<?php
/**
 * A single place to get cache path directories.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache;

use RuntimeException;

/**
 * A single place to get cache path directories.
 *
 * @package SolidWP\Performance
 */
final class Cache_Path {

	/**
	 * The cache directory, e.g. /app/wp-content/cache.
	 *
	 * @var string
	 */
	private string $cache_dir;

	/**
	 * @param  string $cache_dir The cache directory, e.g. /app/wp-content/cache.
	 */
	public function __construct( string $cache_dir ) {
		$this->cache_dir = $cache_dir;
	}

	/**
	 * Returns the server path to the cache directory.
	 *
	 * @example /app/wp-content/cache
	 *
	 * @return string
	 */
	public function get_cache_dir(): string {
		return $this->cache_dir;
	}

	/**
	 * Get the path to where pages are cached.
	 *
	 * @example  /app/wp-content/cache/page
	 *
	 * @return string
	 */
	public function get_page_cache_dir(): string {
		return $this->get_cache_dir() . '/page';
	}

	/**
	 * Get the host site cache directory.
	 *
	 * @example /app/wp-content/cache/page/www.wordpress.test
	 *
	 * @return string
	 */
	public function get_site_cache_dir(): string {
		$path      = $this->get_page_cache_dir();
		$site_host = wp_parse_url( get_site_url(), PHP_URL_HOST );


		return $path . DIRECTORY_SEPARATOR . $site_host;
	}

	/**
	 * Converts a URL into a directory structure.
	 *
	 * All cached requests will be saved to a directory structure that matches
	 * the relative URL. This provides a way to consistently get the cached
	 * resource from the URL.
	 *
	 * @example /app/wp-content/cache/page/local.test.com/test-post
	 *
	 * @throws RuntimeException When URL does not have valid host.
	 *
	 * @param string $url The full URL of the request (e.g. https://solidwp.com/about).
	 *
	 * @return string
	 */
	public function get_path_from_url( string $url ): string {
		$url_parts = parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

		if ( ! isset( $url_parts['host'] ) || $url_parts['host'] === '' ) {
			throw new RuntimeException( 'URL needs a valid host.' );
		}

		$host = trim( $url_parts['host'], '/' );
		$path = trim( $url_parts['path'] ?? '', '/' );

		if ( $path === '' ) {
			$path = 'index';
		}

		return "{$this->get_page_cache_dir()}/$host/$path";
	}
}
