<?php
/**
 * The main plugin class.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance;

use InvalidArgumentException;
use RuntimeException;
use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Page_Cache\Cache_Path;
use SolidWP\Performance\StellarWP\ContainerContract\ContainerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The primary class responsible for booting up the plugin.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
final class Core {

	public const PLUGIN_FILE         = 'solid_performance.plugin_file';
	public const PLUGIN_DIR          = 'solid_performance.plugin_dir';
	public const PLUGIN_URL          = 'solid_performance.plugin_url';
	public const PLUGIN_BASENAME     = 'solid_performance.plugin_basename';
	public const PLUGIN_VERSION      = 'solid_performance.plugin_version';
	public const ADVANCED_CACHE_PATH = 'solid_performance.advanced_cache_path';

	/**
	 * The server path to the plugin's main file.
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * The centralized container.
	 *
	 * @since 0.1.0
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * An array of providers to register within the container before
	 * WordPress is fully bootstrapped, e.g. in advanced-cache.php.
	 *
	 * @var array<int, class-string<Service_Provider>>
	 */
	private array $early_providers = [
		Config\Provider::class,
		Update\Provider::class,
		View\Provider::class,
		Page_Cache\Provider::class,
	];

	/**
	 * An array of providers to register within the container after
	 * WordPress is fully bootstrapped.
	 *
	 * @since 0.1.0
	 *
	 * @var array<int, class-string<Service_Provider>>
	 */
	private array $providers = [
		Assets\Provider::class,
		Page_Cache\Purge\Provider::class,
		Admin\Provider::class,
		API\Provider::class,
		Notices\Provider::class,
		Pipelines\Provider::class,
		Telemetry\Provider::class,
		WP_CLI\Provider::class,
		Shutdown\Provider::class,
	];

	/**
	 * The singleton instance for the plugin.
	 *
	 * @var Core
	 */
	private static self $instance;

	/**
	 * @param  string    $plugin_file The full server path to the main plugin file.
	 * @param  Container $container The container instance.
	 */
	private function __construct(
		string $plugin_file,
		Container $container
	) {
		$this->plugin_file = $plugin_file;
		$this->container   = $container;
		$this->container->singleton( \SolidWP\Performance\Psr\Container\ContainerInterface::class, $this->container );
		$this->container->singleton( SolidWP\Performance\Psr\Container\ContainerInterface::class, $this->container );
		$this->container->singleton( ContainerInterface::class, $this->container );

		// Set container variables available to pre bootstrap providers.
		$this->container->setVar( self::PLUGIN_FILE, $this->plugin_file );
		$this->container->setVar( self::PLUGIN_VERSION, $this->get_plugin_version() );
		$this->container->setVar( self::ADVANCED_CACHE_PATH, WP_CONTENT_DIR . '/advanced-cache.php' );

		// Set the cache dir early so all providers can use it.
		$this->container->when( Cache_Path::class )
						->needs( '$cache_dir' )
						->give( WP_CONTENT_DIR . '/cache' );

		// Register pre bootstrap providers early.
		foreach ( $this->early_providers as $provider ) {
			$this->container->get( $provider )->register();
		}
	}

	/**
	 * Get the singleton instance of our plugin.
	 *
	 * @param  string|null    $plugin_file  The full server path to the main plugin file.
	 * @param  Container|null $container    The container instance.
	 *
	 * @throws InvalidArgumentException If no existing instance and no plugin file or container is provided.
	 *
	 * @return Core
	 */
	public static function instance( ?string $plugin_file = null, ?Container $container = null ): Core {
		if ( ! isset( self::$instance ) ) {
			if ( ! $plugin_file ) {
				throw new InvalidArgumentException( 'You must provide a $plugin_file path' );
			}

			if ( ! $container ) {
				throw new InvalidArgumentException( sprintf( 'You must provide a %s instance!', Container::class ) );
			}

			self::$instance = new self( $plugin_file, $container );
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	public function init(): void {
		// Set remaining container variables now that WordPress is fully bootstrapped.
		$this->container->setVar( self::PLUGIN_DIR, plugin_dir_path( $this->plugin_file ) );
		$this->container->setVar( self::PLUGIN_URL, plugin_dir_url( $this->plugin_file ) );
		$this->container->setVar( self::PLUGIN_BASENAME, plugin_basename( $this->plugin_file ) );

		// Register remaining providers now that WP is fully bootstrapped.
		foreach ( $this->providers as $class ) {
			$this->container->get( $class )->register( $this->container );
		}
	}

	/**
	 * Returns the container instance.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Prevent wakeup.
	 *
	 * @throws RuntimeException When attempting to wake up this instance.
	 */
	public function __wakeup(): void {
		throw new RuntimeException( 'method not implemented' );
	}

	/**
	 * Prevent sleep.
	 *
	 * @throws RuntimeException When attempting to sleep this instance.
	 */
	public function __sleep(): array {
		throw new RuntimeException( 'method not implemented' );
	}

	/**
	 * Prevent cloning.
	 *
	 * @throws RuntimeException When attempting to clone this instance.
	 */
	private function __clone() {
		throw new RuntimeException( 'Cloning not allowed' );
	}

	/**
	 * Get the plugin's current version.
	 *
	 * @throws RuntimeException If we are unable to retrieve the plugin version.
	 *
	 * @return string
	 */
	private function get_plugin_version(): string {
		$content = file_get_contents( $this->plugin_file, false, null, 0, 8 * KB_IN_BYTES );

		$pattern = '/^ \* Version:\s*(.+)$/m';

		if ( preg_match( $pattern, $content, $matches ) ) {
			return trim( $matches[1] );
		}

		throw new RuntimeException( sprintf( 'Unable to read plugin version from "%s"', $this->plugin_file ) );
	}
}
