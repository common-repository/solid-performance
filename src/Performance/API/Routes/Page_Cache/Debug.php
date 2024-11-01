<?php
/**
 * The route for clearing the page cache.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\API\Routes\Page_Cache;

use SolidWP\Performance\API\Base_Route;
use SolidWP\Performance\Page_Cache;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

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
class Debug extends Base_Route {

	/**
	 * @var Page_Cache
	 */
	private Page_Cache $page_cache;

	/**
	 * @param  Page_Cache $page_cache The page cache object.
	 */
	public function __construct( Page_Cache $page_cache ) {
		$this->page_cache = $page_cache;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_path(): string {
		return '/page/debug';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_methods() {
		return [
			WP_REST_Server::READABLE,
			WP_REST_Server::CREATABLE,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function callback( WP_REST_Request $request ) {

		// Return the current status for a GET request.
		if ( $request->get_method() === WP_REST_Server::READABLE ) {
			return $this->status_response();
		}

		$state = $request->get_param( 'state' ) === 'on';

		return $this->update_state( $state );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arguments(): array {
		return [
			'state' => [
				'type'        => 'string',
				'description' => 'The state of the page cache debug mode.',
				'enum'        => [
					'on',
					'off',
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema_callback(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'setting',
			'type'       => 'object',
			'properties' => [
				'enabled' => [
					'description' => esc_html__( 'The status of the page cache.', 'solid-performance' ),
					'type'        => 'boolean',
					'readonly'    => true,
				],
				'code'    => [
					'description' => esc_html__( 'The identification code of the setting state.', 'solid-performance' ),
					'type'        => 'string',
					'enum'        => [
						'solid_performance_page_cache_debug_mode_on',
						'solid_performance_page_cache_debug_mode_off',
					],
					'readonly'    => true,
				],
				'message' => [
					'description' => esc_html__( 'The formatted message of the setting state.', 'solid-performance' ),
					'type'        => 'string',
					'readonly'    => true,
				],
			],
		];
	}

	/**
	 * Returns the status of page cache debug mode.
	 *
	 * @since 0.1.0
	 *
	 * @return WP_REST_Response
	 */
	protected function status_response(): WP_REST_Response {
		$status = $this->page_cache->is_debug_on();

		if ( $status ) {
			return new WP_REST_Response(
				[
					'enabled' => true,
					'code'    => 'solid_performance_page_cache_debug_mode_on',
					'message' => 'Page cache debug mode is enabled',
				]
			);
		}

		return new WP_REST_Response(
			[
				'enabled' => false,
				'code'    => 'solid_performance_page_cache_debug_mode_off',
				'message' => 'Page cache debug mode is disabled',
			]
		);
	}

	/**
	 * Changes the state of debug mode and returns a response.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $state The state to change debug mode to.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	protected function update_state( bool $state ) {
		$result = $this->page_cache->debug( $state );

		if ( $result ) {
			return new WP_REST_Response(
				[
					'enabled' => true,
					'code'    => 'solid_performance_page_cache_debug_mode_on',
					'message' => 'Page cache debug mode is enabled',
				]
			);
		}

		return new WP_REST_Response(
			[
				'enabled' => false,
				'code'    => 'solid_performance_page_cache_debug_mode_off',
				'message' => 'Page cache debug mode is disabled',
			]
		);
	}
}
