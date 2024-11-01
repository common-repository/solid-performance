<?php
/**
 * The provider for all WP_CLI functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\WP_CLI;

use SolidWP\Performance\Contracts\Service_Provider;
use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all WP_CLI functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Provider extends Service_Provider {

	/**
	 * An array of commands that should be registered.
	 *
	 * @var array<string,string>
	 */
	private array $commands = [
		'perf' => Performance::class,
	];

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI_Command' ) ) {
			foreach ( $this->commands as $name => $class ) {
				WP_CLI::add_command( "solid $name", $this->container->get( $class ) );
			}
		}
	}
}
