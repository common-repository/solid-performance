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
 * @package SolidWP\Performance
 */
class Status extends Base_Route {

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
		return '/page/status';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_methods() {
		return WP_REST_Server::READABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function callback( WP_REST_Request $request ) {
		$result = $this->page_cache->is_on();

		if ( $result ) {
			return new WP_REST_Response(
				[
					'enabled' => true,
					'code'    => 'solid_performance_page_cache_on',
					'message' => 'Page caching is currently enabled',
				]
			);
		}

		return new WP_REST_Response(
			[
				'enabled' => false,
				'code'    => 'solid_performance_page_cache_off',
				'message' => 'Page caching is currently disabled',
			]
		);
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
					'description' => esc_html__( 'The current status of the page cache.', 'solid-performance' ),
					'type'        => 'boolean',
					'readonly'    => true,
				],
				'code'    => [
					'description' => esc_html__( 'The identification code of the setting state.', 'solid-performance' ),
					'type'        => 'string',
					'enum'        => [
						'solid_performance_page_cache_on',
						'solid_performance_page_cache_off',
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
}
