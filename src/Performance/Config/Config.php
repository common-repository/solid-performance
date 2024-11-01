<?php
/**
 * The configuration class to all and set configuration/settings, as well as fire off
 * save events.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config;

use SolidWP\Performance\Shutdown\Contracts\Terminable;
use SolidWP\Performance\StellarWP\Arrays\Arr;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The configuration class to all and set configuration/settings, as well as fire off
 * save events.
 *
 * This object is loaded as a singleton, so its values when accessed will
 * match the current state.
 *
 * @package SolidWP\Performance
 */
final class Config implements Terminable {

	/**
	 * All configuration items.
	 *
	 * @var array<string, mixed>
	 */
	private array $items;

	/**
	 * @var Config_Loader
	 */
	private Config_Loader $loader;

	/**
	 * Whether we're queued up to save on shutdown.
	 *
	 * @var bool
	 */
	private bool $will_save = false;

	/**
	 * @param  Config_Loader $loader The configuration loader.
	 */
	public function __construct( Config_Loader $loader ) {
		$this->loader = $loader;
		$this->items  = $this->loader->load();
	}

	/**
	 * Get all the current configuration items.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Get a configuration value.
	 *
	 * @param  string $key The config key.
	 *
	 * @return mixed
	 */
	public function get( string $key ) {
		return Arr::get( $this->items, explode( '.', $key ) );
	}

	/**
	 * Sets a config item in memory.
	 *
	 * @param  string $key The key to set.
	 * @param  mixed  $value The value to set.
	 *
	 * @return $this
	 */
	public function set( string $key, $value ): Config {
		$this->items = Arr::set( $this->items, explode( '.', $key ), $value );

		return $this;
	}

	/**
	 * Queue up config to save its state to the database on shutdown.
	 *
	 * This prevents writing to the database / config file multiple times.
	 *
	 * @return $this
	 */
	public function queue(): Config {
		$this->will_save = true;

		return $this;
	}

	/**
	 * Manually call hooks that perform config database saves.
	 *
	 * @return void
	 */
	public function save(): void {
		do_action( 'solidwp/performance/config/save' );
	}

	/**
	 * Save all changes on shutdown.
	 *
	 * @see \SolidWP\Performance\Shutdown\Provider::register()
	 *
	 * @action shutdown
	 * @action solidwp/performance/terminate
	 *
	 * @return void
	 */
	public function terminate(): void {
		if ( ! $this->will_save ) {
			return;
		}

		$this->save();

		// Shutdown terminable tasks run twice, we only need to save once.
		$this->will_save = false;
	}

	/**
	 * Refresh the configuration state, as the database values may have changed
	 * based on where we are in the request lifecycle.
	 *
	 * @return $this
	 */
	public function refresh(): Config {
		$this->items = $this->loader->load();

		return $this;
	}
}
