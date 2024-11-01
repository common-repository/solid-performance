<?php
/**
 * Registers configuration definitions in the container.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Config;

use SolidWP\Performance\Config\Writers\File_Writer;
use SolidWP\Performance\Config\Writers\Network_Option_Writer;
use SolidWP\Performance\Config\Writers\Option_Writer;
use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Core;
use SolidWP\Performance\Page_Cache\Cache_Path;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers config definitions in the container.
 *
 * @note This is loaded earlier than other providers.
 *
 * @see Core::__construct()
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * The option we key use to save to wp_options/wp_sitemeta.
	 *
	 * @var string
	 */
	private string $option_key = Config_Loader::OPTION_KEY;

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		$this->container->when( Advanced_Cache::class )
						->needs( '$version' )
						->give( static fn( $c ): string => $c->get( Core::PLUGIN_VERSION ) );

		$this->container->when( Advanced_Cache::class )
						->needs( '$destination' )
						->give( static fn( $c ): string => $c->get( Core::ADVANCED_CACHE_PATH ) );

		$this->container->when( Config_File::class )
						->needs( '$dir' )
						->give( static fn(): string => WP_CONTENT_DIR . '/cache/solid-performance' );

		$this->container->when( Default_Config::class )
						->needs( '$defaults' )
						->give(
							// Configure the default configuration items here.
							static fn( $c ): array => [
								'page_cache' => [
									'cache_dir'   => $c->get( Cache_Path::class )->get_cache_dir(),
									'debug'       => false,
									'enabled'     => true,
									'expiration'  => 86400, // One day in seconds.
									'exclusions'  => [],
									'compression' => [
										'enabled' => true,
									],
								],
							]
						);

		$this->container->singleton( Config_File::class, Config_File::class );
		$this->container->singleton( Default_Config::class, Default_Config::class );
		$this->container->singleton( Config::class, Config::class );

		// Refresh the configuration once the database is available.
		add_action( 'plugins_loaded', $this->container->callback( Config::class, 'refresh' ), 11 );

		// Sync to the configuration file each time our settings are saved in the database.
		add_action(
			"add_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		add_action(
			"update_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		add_action(
			"delete_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		// Manually write to wp_options when $config->save() is called.
		add_action( 'solidwp/performance/config/save', $this->container->callback( Option_Writer::class, 'save' ) );

		// Sync the config file.
		add_action( 'solidwp/performance/config/write_file', $this->container->callback( File_Writer::class, 'save' ) );

		$this->register_multisite_configuration();
	}

	/**
	 * Register additional events if we're running on multisite.
	 *
	 * @note This is an incomplete feature, as we currently have no MS settings screen.
	 *
	 * @return void
	 */
	private function register_multisite_configuration(): void {
		if ( ! is_multisite() ) {
			return;
		}
		// Sync to the configuration file each time our settings are saved in the database.
		add_action(
			"add_site_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		add_action(
			"update_site_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		add_action(
			"delete_site_option_{$this->option_key}",
			function (): void {
				do_action( 'solidwp/performance/config/write_file' );
			},
		);

		// Write to wp_network_options when $config->save() is called.
		add_action( 'solidwp/performance/config/save', $this->container->callback( Network_Option_Writer::class, 'save' ) );
	}
}
