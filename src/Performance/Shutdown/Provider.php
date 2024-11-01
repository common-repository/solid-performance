<?php
/**
 * The Service Provider for Terminal Shutdown tasks.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown;

use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Page_Cache\Purge\Batch\Batch_Purger;
use SolidWP\Performance\Shutdown\Rules\Query_Monitor_Rule;
use SolidWP\Performance\Update\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Service Provider for Terminal Shutdown tasks.
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->register_priority_rules();

		$this->container->singleton(
			Shutdown_Handler::class,
			fn() => new Shutdown_Handler(
				// Add any terminable tasks to the collection to run on shutdown.
				// Important: these will run in the order provided.
				$this->container->get( Config::class ),
				$this->container->get( Updater::class ),
				$this->container->get( Batch_Purger::class ),
			)
		);

		// Create an action that can be called manually.
		add_action( 'solidwp/performance/terminate', $this->container->callback( Shutdown_Handler::class, 'handle' ) );

		// Run early to get ahead of any potentially bad code that could kill execution.
		add_action(
			'shutdown',
			static function (): void {
				do_action( 'solidwp/performance/terminate' );
			},
			$this->container->get( Priority_Selector::class )->get_priority()
		);

		// Run this again in case any other code was updated in the shutdown action that could trigger our Terminable tasks.
		add_action(
			'shutdown',
			static function (): void {
				do_action( 'solidwp/performance/terminate' );
			},
			9999
		);
	}

	/**
	 * Register priority rules to determine the initial shutdown hook priority depending on which
	 * other plugins are active.
	 *
	 * @return void
	 */
	private function register_priority_rules(): void {
		$this->container->when( Priority_Selector::class )
						->needs( '$rules' )
						->give(
							fn(): array => [
								$this->container->get( Query_Monitor_Rule::class ),
							]
						);
	}
}
