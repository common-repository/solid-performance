<?php
/**
 * The provider for all telemetry related functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Telemetry;

use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Core;
use SolidWP\Performance\StellarWP\Telemetry\Config as TelemetryConfig;
use SolidWP\Performance\StellarWP\Telemetry\Core as Telemetry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The provider for all telemetry related functionality.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Provider extends Service_Provider {

	/**
	 * {@inheritdoc}
	 */
	public function register(): void {
		TelemetryConfig::set_container( $this->container );
		TelemetryConfig::set_server_url( 'https://telemetry.stellarwp.com/api/v1' );
		TelemetryConfig::set_hook_prefix( 'solid-performance' );
		TelemetryConfig::set_stellar_slug( 'solid-performance' );
		Telemetry::instance()->init( $this->container->getVar( Core::PLUGIN_FILE ) );

		add_filter( 'stellarwp/telemetry/optin_args', $this->container->callback( Modal::class, 'optin_args' ), 10, 2 );
		add_filter( 'debug_information', $this->container->callback( Health_Data::class, 'add_summary_to_telemetry' ), 10, 1 );
	}
}
