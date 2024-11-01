<?php
/**
 * The contract defining required methods for a REST route.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\API;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The contract defining required methods for a REST route.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
interface Route {
	/**
	 * Get the path pattern of the REST API endpoint (e.g. '/author/(?P<id>\d+)').
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_path(): string;

	/**
	 * Gets the HTTP methods that the REST API endpoint responds to.
	 *
	 * @since 0.1.0
	 *
	 * @return mixed
	 */
	public function get_methods();

	/**
	 * The action performed by the REST API endpoint to send a response.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request The current request for the route.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function callback( WP_REST_Request $request );

	/**
	 * Gets the expected arguments for the REST API endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_arguments(): array;

	/**
	 * Ensures user can make a request to the REST API endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function permission_callback(): bool;

	/**
	 * The callback used to output schema for the route.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function schema_callback(): array;
}
