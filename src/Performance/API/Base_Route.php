<?php
/**
 * An abstract route for all other routes to extend.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\API;

use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class that handles the clear cache route.
 *
 * @since 0.1.0
 *
 * @package SolidWP|Performance
 */
abstract class Base_Route implements Route {

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_path(): string;

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_methods();

	/**
	 * {@inheritdoc}
	 */
	abstract public function callback( WP_REST_Request $request );

	/**
	 * [@inheritdoc]
	 */
	abstract public function schema_callback(): array;

	/**
	 * {@inheritdoc}
	 */
	public function get_arguments(): array {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_options' );
	}
}
