<?php
/**
 * Handles all functionality related to storing and returning files in the page cache directory.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache;

use Exception;
use SolidWP\Performance\Container;
use SolidWP\Performance\Http\Request;
use SolidWP\Performance\Pipelines\Page_Cache;
use SolidWP\Performance\StellarWP\Pipeline\Pipeline;
use SolidWP\Performance\StellarWP\SuperGlobals\SuperGlobals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all functionality related to storing and returning files in the page cache directory.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Cache {

	/**
	 * The current request.
	 *
	 * @since 0.1.0
	 *
	 * @var Request
	 */
	private Request $request;

	/**
	 * @var Container
	 */
	private Container $container;

	/**
	 * @var Cache_Handler
	 */
	private Cache_Handler $handler;

	/**
	 * The pipes to send all requests through before serving an existing cached page.
	 *
	 * @context pre-wordpress
	 *
	 * @since 0.1.0
	 *
	 * @var array<int,string>
	 */
	private array $serve_pipes = [
		Page_Cache\Response_Code::class,
		Page_Cache\Constant::class,
		Page_Cache\Admin::class,
		Page_Cache\Authenticated::class,
		Page_Cache\Method::class,
		Page_Cache\Path::class,
		Page_Cache\Query::class,
		Page_Cache\Exclusion::class,

		// Third Party Exclusions.
		Page_Cache\Third_Party_Exclusions\GiveWP::class,
		Page_Cache\Third_Party_Exclusions\WooCommerce::class,
	];

	/**
	 * The pipes to send new requests through before saving them to the cache directory.
	 *
	 * @context after-wordpress
	 *
	 * @since 0.1.0
	 *
	 * @var array<int,string>
	 */
	private array $save_pipes = [
		Page_Cache\Response_Code::class,
		Page_Cache\Constant::class,
		Page_Cache\Admin::class,
		Page_Cache\Authenticated::class,
		Page_Cache\Content_Type::class,
		Page_Cache\Wp::class,
		Page_Cache\Method::class,
		Page_Cache\Path::class,
		Page_Cache\Post::class,
		Page_Cache\Query::class,
		Page_Cache\Exclusion::class,

		// Third Party Exclusions.
		Page_Cache\Third_Party_Exclusions\GiveWP::class,
		Page_Cache\Third_Party_Exclusions\WooCommerce::class,
	];

	/**
	 * The class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param  Request       $request         The current request.
	 * @param  Container     $container       The DI container.
	 * @param  Cache_Handler $handler       The Cache Handler.
	 */
	public function __construct(
		Request $request,
		Container $container,
		Cache_Handler $handler
	) {
		$this->request   = $request;
		$this->container = $container;
		$this->handler   = $handler;
	}

	/**
	 * Returns the cached file to the browser if it exists.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function serve_cached_file(): void {
		if ( ! $this->should_serve_cached_file() ) {
			return;
		}

		// Cache file doesn't exist or is expired.
		if ( ! $this->handler->is_valid() ) {
			return;
		}

		// Send Content-Encoding headers for compression, if any.
		$this->handler->compressor()->send_headers();

		$cache_file = $this->handler->get_file_path();

		// If the user is checking their version of the cached resource.
		$mod_time = filemtime( $cache_file );

		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $mod_time ) . ' GMT' );

		$modified_since = SuperGlobals::get_server_var( 'HTTP_IF_MODIFIED_SINCE', '' );

		header( sprintf( 'X-Cache-Age: %d', time() - $mod_time ) );
		header( 'X-Cached-By: Solid Performance' );

		if ( $modified_since && strtotime( $modified_since ) === $mod_time ) {
			// Cache is current, just respond with 304 headers.
			header( SuperGlobals::get_server_var( 'SERVER_PROTOCOL', '' ) . ' 304 Not Modified', true, 304 );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-cache, must-revalidate' );
			exit;
		}

		readfile( $cache_file );
		exit;
	}

	/**
	 * Saves content to a new file in the cache.
	 *
	 * @since 0.1.0
	 *
	 * @throws Exception When cache cannot be saved or the cache directory can't be created.
	 *
	 * @param string $output Output to save in the cached file.
	 *
	 * @return void
	 */
	public function save_output( string $output ): void {
		// Don't save empty buffers.
		if ( $output === '' ) {
			return;
		}

		// Check if the current request should be cached.
		if ( ! $this->should_save_cached_file() ) {
			return;
		}

		$this->handler->save( $output );
	}

	/**
	 * Determines if the current request should be served an existing cached page.
	 *
	 * If any pipe returns false, the request will not be served from existing cache.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private function should_serve_cached_file(): bool {
		$pipeline = new Pipeline( $this->container );
		$context  = new WP_Context( $this->request );

		return $pipeline
				->send( $context )
				->through( $this->serve_pipes )
				->then(
					function () {
						// If we make it through the pipeline, we should cache the request.
						return true;
					}
				);
	}

	/**
	 * Run through pipeline and determine if the current request should be saved to the cache directory.
	 *
	 * If any middleware return false, the current request will not be cached (or served from existing cache).
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private function should_save_cached_file(): bool {
		$pipeline = new Pipeline( $this->container );
		$context  = $this->container->get( WP_Context::class );

		return $pipeline
				->send( $context )
				->through( $this->save_pipes )
				->then(
					function () use ( $context ) {
						/**
						 * If the request makes it through this pipeline, the request should be saved.
						 *
						 * However, since this is fired within a loaded WordPress context, developers can optionally hook in and determine if the response should be saved.
						 *
						 * @param WP_Context $context The context of the current request.
						 *
						 * @return bool
						 */
						return apply_filters( 'solidwp/performance/should_save_cached_file', true, $context );
					}
				);
	}
}
