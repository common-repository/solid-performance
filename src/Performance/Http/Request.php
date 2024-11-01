<?php
/**
 * A basic request.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Http;

use SolidWP\Performance\StellarWP\SuperGlobals\SuperGlobals;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A class representing a request.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Request {

	/**
	 * The URI of the request.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public string $uri;

	/**
	 * The request path.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public string $path;

	/**
	 * The request query string.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public string $query;

	/**
	 * The time the request was captured.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public string $start;

	/**
	 * The global $_SERVER variable.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public array $server;

	/**
	 * The global $_GET variable.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public array $get;

	/**
	 * The global $_POST variable.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public array $post;

	/**
	 * The global $_ENV variable.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public array $env;

	/**
	 * The global $_COOKIE variable.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public array $cookie;

	/**
	 * The method of the request.
	 *
	 * @var string
	 */
	public string $method;

	/**
	 * The HTTP Request headers.
	 *
	 * @var Header
	 */
	public Header $header;

	/**
	 * The class constructor.
	 *
	 * @param string $uri The URI for the request.
	 *
	 * @since 0.1.0
	 */
	public function __construct( string $uri = '' ) {
		$this->set_start();
		$this->set_uri( $uri );
		$this->set_query();
		$this->set_path();
		$this->set_globals();

		$this->header = new Header( getallheaders() );
	}

	/**
	 * Sets the micro timestamp to mark the beginning of the request.
	 *
	 * @since 0.1.0
	 *
	 * @param string $start The timestamp used to mark the beginning of the request.
	 *
	 * @return void
	 */
	public function set_start( string $start = '' ): void {

		if ( strlen( $start ) > 0 ) {
			$this->start = $start;
			return;
		}

		$this->start = floatval( $this->server['REQUEST_TIME_FLOAT'] ?? microtime() );
	}

	/**
	 * Sets the request URI.
	 *
	 * @since 0.1.0
	 *
	 * @param string $uri The full URI for the request.
	 *
	 * @return void
	 */
	public function set_uri( string $uri = '' ): void {

		if ( strlen( $uri ) > 0 ) {
			$this->uri = $uri;
			return;
		}

		$protocol = 'http';

		if ( SuperGlobals::get_server_var( 'HTTPS', 'off' ) === 'on' ) {
			$protocol = 'https';
		}

		$host = SuperGlobals::get_server_var( 'HTTP_HOST', '' );
		$path = SuperGlobals::get_server_var( 'REQUEST_URI', '' );

		$this->uri = $protocol . '://' . $host . $path;
	}

	/**
	 * Sets the path from the URI.
	 *
	 * @since 0.1.0
	 *
	 * @param string $path The path to set for the request.
	 *
	 * @return void
	 */
	public function set_path( string $path = '' ): void {
		if ( $path === '' ) {
			$this->path = parse_url( $this->uri, PHP_URL_PATH ) ?: ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			return;
		}

		$this->path = $path;
	}

	/**
	 * Sets the query string for the current request.
	 *
	 * @since 0.1.0
	 *
	 * @param string $query The query string to set for the request.
	 *
	 * @return void
	 */
	public function set_query( string $query = '' ): void {
		if ( $query === '' ) {
			$this->query = parse_url( $this->uri, PHP_URL_QUERY ) ?? ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			return;
		}

		$this->query = $query;
	}

	/**
	 * Sets the associated global variables.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $server  The SERVER superglobal array for the request.
	 * @param array  $get     The GET superglobal array for the request.
	 * @param array  $post    The POST superglobal array for the request.
	 * @param array  $env     The ENV superglobal array for the request.
	 * @param array  $cookie  The COOKIE superglobal array for the request.
	 * @param string $method The REQUEST_METHOD for the request.
	 *
	 * @return void
	 */
	public function set_globals( array $server = [], array $get = [], array $post = [], array $env = [], array $cookie = [], string $method = '' ): void {
		$this->server = $server ?: SuperGlobals::get_raw_superglobal( 'SERVER' );
		$this->get    = $get ?: SuperGlobals::get_raw_superglobal( 'GET' );
		$this->post   = $post ?: SuperGlobals::get_raw_superglobal( 'POST' );
		$this->env    = $env ?: SuperGlobals::get_raw_superglobal( 'ENV' );
		$this->cookie = $cookie ?: SuperGlobals::get_raw_superglobal( 'COOKIE' );
		$this->method = $method ?: SuperGlobals::get_server_var( 'REQUEST_METHOD', '' );
	}
}
