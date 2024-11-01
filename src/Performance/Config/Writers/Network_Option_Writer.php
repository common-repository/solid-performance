<?php
/**
 * Writes config items to the network sitemeta table.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config\Writers;

use RuntimeException;
use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Config\Config_Loader;
use SolidWP\Performance\Config\Writers\Contracts\Writable;

/**
 * Writes config items to the network sitemeta table.
 *
 * @package SolidWP\Performance
 */
final class Network_Option_Writer implements Writable {

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @param  Config $config The config singleton.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Save config to the sitemeta table, as long as multisite is enabled.
	 *
	 * @action solidwp/performance/config/save
	 *
	 * @throws RuntimeException If trying to save before WordPress is fully bootstrapped.
	 *
	 * @return bool
	 */
	public function save(): bool {
		if ( ! is_multisite() ) {
			return false;
		}

		if ( ! function_exists( 'update_network_option' ) ) {
			throw new RuntimeException(
				sprintf(
					'%s::save() was called too early in the execution. Wait until plugins_loaded has fired.',
					Config::class
				)
			);
		}

		$items = $this->config->all();

		return update_network_option( get_main_network_id(), Config_Loader::OPTION_KEY, $items );
	}
}
