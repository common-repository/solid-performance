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
class Regenerate extends Base_Route {

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
		return '/page/regenerate';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_methods() {
		return WP_REST_Server::CREATABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function callback( WP_REST_Request $request ) {
		$result = $this->page_cache->regenerate_advanced_cache();

		if ( ! $result ) {
			return new WP_Error(
				'solid_performance_advanced_cache_not_regenerated',
				'Advanced cache file not regenerated',
			);
		}

		return new WP_REST_Response(
			[
				'code'    => 'solid_performance_advanced_cache_regenerated',
				'message' => 'Advanced cache file regenerated',
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
				'code'    => [
					'description' => esc_html__( 'The identification code of the advanced cache regeneration action.', 'solid-performance' ),
					'type'        => 'string',
					'enum'        => [
						'solid_performance_advanced_cache_not_regenerated',
						'solid_performance_advanced_cache_regenerated',
					],
					'readonly'    => true,
				],
				'message' => [
					'description' => esc_html__( 'The formatted message of the advanced cache regeneration action.', 'solid-performance' ),
					'type'        => 'string',
					'readonly'    => true,
				],
			],
		];
	}
}
