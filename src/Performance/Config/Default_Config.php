<?php
/**
 * Contains the default configuration items.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config;

use SolidWP\Performance\StellarWP\Arrays\Arr;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains the default configuration items.
 *
 * @template T of array{page_cache: array{cache_dir: string, debug: bool, enabled: bool, expiration: int, exclusions: string[]}}
 *
 * @see Provider::register()
 *
 * @package SolidWP\Performance
 */
final class Default_Config {

	/**
	 * The default configuration items.
	 *
	 * @var T
	 */
	private array $defaults;

	/**
	 * @param  array<string, mixed> $defaults The default config items.
	 */
	public function __construct( array $defaults ) {
		$this->defaults = $defaults;
	}

	/**
	 * Get the value of a default config item.
	 *
	 * @param  string|null $key  The config key, if no key we'll return all items.
	 *
	 * @return mixed|T
	 */
	public function get( ?string $key = null ) {
		if ( is_null( $key ) ) {
			return $this->defaults;
		}

		return Arr::get( $this->defaults, explode( '.', $key ) );
	}
}
