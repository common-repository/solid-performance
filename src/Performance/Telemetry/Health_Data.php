<?php
/**
 * Handles adding a new section to the site health data screen.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Telemetry;

use SolidWP\Performance\Page_Cache\Compression\Collection;
use SolidWP\Performance\Page_Cache\Compression\Contracts\Compressible;

/**
 * Handles adding a new section to the site health data screen.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Health_Data {

	/**
	 * The collection of compression strategies.
	 *
	 * @var Collection
	 */
	private Collection $compressors;

	/**
	 * @param  Collection $compressors The collection of compression strategies.
	 */
	public function __construct( Collection $compressors ) {
		$this->compressors = $compressors;
	}

	/**
	 * Adds a new Solid Performance section to site health data.
	 *
	 * @since 0.1.0
	 *
	 * @filter debug_information
	 *
	 * @param array $info The array of site health data.
	 *
	 * @return array
	 */
	public function add_summary_to_telemetry( array $info ): array {
		$page_cache          = swpsp_config_get( 'page_cache' );
		$page_cache_status   = $page_cache['enabled'] ? esc_html__( 'Enabled', 'solid-performance' ) : esc_html__( 'Disabled', 'solid-performance' );
		$cache_dir_writeable = swpsp_direct_filesystem()->is_writable( $page_cache['cache_dir'] ) ? esc_html__( 'Writable', 'solid-performance' ) : esc_html__( 'Not Writable', 'solid-performance' );
		$debug_mode_status   = $page_cache['debug'] ? esc_html__( 'Enabled', 'solid-performance' ) : esc_html__( 'Disabled', 'solid-performance' );
		$exclusion_count     = count( $page_cache['exclusions'] );
		$enabled_compressors = implode(
			', ',
			array_filter(
				array_map( static fn( Compressible $c ): string => $c->encoding(), $this->compressors->enabled() )
			)
		);

		$info['solid-performance'] = [
			'label'  => esc_html__( 'Solid Performance', 'solid-performance' ),
			'fields' => [
				'page_cache_status'        => [
					'label' => esc_html__( 'Page cache', 'solid-performance' ),
					'value' => $page_cache_status,
					'debug' => strtolower( $page_cache_status ),
				],
				'cache_directory'          => [
					'label' => esc_html__( 'Cache directory', 'solid-performance' ),
					'value' => $page_cache['cache_dir'],
					'debug' => $page_cache['cache_dir'],
				],
				'cache_directory_writable' => [
					'label' => esc_html__( 'Cache directory permissions', 'solid-performance' ),
					'value' => $cache_dir_writeable,
					'debug' => strtolower( $cache_dir_writeable ),
				],
				'debug_mode'               => [
					'label' => esc_html__( 'Debug mode', 'solid-performance' ),
					'value' => $debug_mode_status,
					'debug' => strtolower( $debug_mode_status ),
				],
				'exclusion_count'          => [
					'label' => esc_html__( 'Number of custom exclusions', 'solid-performance' ),
					'value' => $exclusion_count,
				],
				'compression'              => [
					'label' => esc_html__( 'Supported compression algorithms', 'solid-performance' ),
					'value' => $enabled_compressors,
					'debug' => $enabled_compressors,
				],
			],
		];

		return $info;
	}
}
