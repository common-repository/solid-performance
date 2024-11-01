<?php
/**
 * A class responsible for purging cached files.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Page_Cache\Purge;

use RuntimeException;
use SolidWP\Performance\Page_Cache\Cache_Path;
use SolidWP\Performance\Page_Cache\Compression\Collection;
use SolidWP\Performance\Page_Cache\Purge\Batch\Permalink;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;
use SolidWP\Performance\Page_Cache\Purge\Traits\With_Permalink;
use WP_Filesystem_Direct;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A class responsible for purging cached files.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Purge {

	use With_Permalink;

	/**
	 * The Cache Path.
	 *
	 * @var Cache_Path
	 */
	private Cache_Path $cache_path;

	/**
	 * @var Collection
	 */
	private Collection $compressors;

	/**
	 * An instance of the native WordPress filesystem.
	 *
	 * @since 0.1.0
	 *
	 * @var WP_Filesystem_Direct
	 */
	private WP_Filesystem_Direct $filesystem;

	/**
	 * The class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param  Cache_Path $cache_path  The cache path.
	 * @param  Collection $compressors  The collection of compression strategies.
	 */
	public function __construct( Cache_Path $cache_path, Collection $compressors ) {
		$this->cache_path  = $cache_path;
		$this->compressors = $compressors;
		$this->filesystem  = swpsp_direct_filesystem();
	}

	/**
	 * Removes a single page from the cache directory.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $url  The full url of the page to purge from the cache.
	 *
	 * @throws RuntimeException If no valid host is found.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function page( string $url ): bool {
		if ( ! $this->is_pretty( $url ) ) {
			return false;
		}

		$path = $this->cache_path->get_path_from_url( $url );

		foreach ( $this->compressors->enabled() as $compressor ) {
			$ext = $compressor->extension();

			$this->filesystem->delete( "$path.$ext" );
		}

		return true;
	}

	/**
	 * Purge all paginated cache files for a base URL.
	 *
	 * For example, if you pass `https://wordpress.test/author/admin/`, then we would
	 * purge `/app/wp-content/cache/page/wordpress.test/author/admin/page/*` and
	 * `/app/wp-content/cache/page/wordpress.test/author/admin/comment-page/*` depending
	 * on the configured rewrites.
	 *
	 * @param  string $url The base URL.
	 *
	 * @throws RuntimeException When deleting a paginated cache folder fails, if that folder exists.
	 *
	 * @return void
	 */
	public function pagination( string $url ): void {
		if ( ! $this->is_pretty( $url ) ) {
			return;
		}

		$path = $this->cache_path->get_path_from_url( $url );

		// If this is the home page, strip off /index for a proper pagination path.
		if ( str_ends_with( $path, '/index' ) ) {
			$path = substr( $path, 0, - strlen( '/index' ) );
		}

		// Collect any pagination bases.
		global $wp_rewrite;

		$bases = array_filter(
			[
				$wp_rewrite->pagination_base ?? false,
				$wp_rewrite->comments_pagination_base ?? false,
			]
		);

		foreach ( $bases as $base ) {
			$paginated_dir = "$path/$base";

			if ( ! $this->filesystem->is_dir( $paginated_dir ) ) {
				continue;
			}

			if ( ! $this->filesystem->delete( $paginated_dir, true ) ) {
				throw new RuntimeException( sprintf( 'Failed to delete pagination path "%s"', $paginated_dir ) );
			}
		}
	}

	/**
	 * Purges a URL along with its paginated cached pages.
	 *
	 * @param  string $url The URL.
	 *
	 * @throws RuntimeException When deleting a paginated cache folder fails, if that folder exists.
	 *
	 * @return bool
	 */
	public function page_with_pagination( string $url ): bool {
		// Purge the pagination.
		$this->pagination( $url );

		// Purge the main archive page.
		return $this->page( $url );
	}

	/**
	 * Purge a cache file by post ID.
	 *
	 * @param  int $post_id The post ID to purge.
	 *
	 * @throws RuntimeException If no valid host is found.
	 *
	 * @return bool
	 */
	public function by_post_id( int $post_id ): bool {
		$permalink = get_permalink( $post_id );

		if ( ! $permalink ) {
			return false;
		}

		return $this->page( $permalink );
	}

	/**
	 * Purge a permalink object based on its strategy.
	 *
	 * @param  Permalink $permalink The permalink to purge.
	 *
	 * @return bool
	 */
	public function by_permalink( Permalink $permalink ): bool {
		switch ( $permalink->purge_strategy ) {
			case Purge_Strategy::POST_ID:
				return $this->by_post_id( $permalink->object_id );
			case Purge_Strategy::PAGE_WITH_PAGINATION:
				return $this->page_with_pagination( $permalink->permalink );
			case Purge_Strategy::PAGE:
				return $this->page( $permalink->permalink );
			default:
				return false;
		}
	}

	/**
	 * Removes all cached pages from the cache directory.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function all_pages(): bool {
		$dir = $this->cache_path->get_site_cache_dir();

		if ( ! $this->filesystem->is_dir( $dir ) ) {
			// If the directory doesn't exist, provide a successful response.
			return true;
		}

		return $this->filesystem->delete( $dir, true );
	}
}
