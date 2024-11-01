<?php
/**
 * The Service Provider to register updater tasks in the container.
 *
 * This provider is booted early, when advanced-cache.php is.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Update;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use SolidWP\Performance\Contracts\Service_Provider;
use SolidWP\Performance\Core;
use SolidWP\Performance\Flintstone\Flintstone;
use SolidWP\Performance\Update\Tasks\Post_Bootstrap\Advanced_Cache_Restorer;
use SolidWP\Performance\Update\Tasks\Pre_Bootstrap\Advanced_Cache_Remover;

/**
 * The Service Provider to register updater tasks in the container.
 *
 * This provider is booted early, when advanced-cache.php is.
 *
 * @package SolidWP\Performance
 */
final class Provider extends Service_Provider {

	public const DB = 'solid_performance.update_db';

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->register_key_value_store();
		$this->register_updater();
	}

	/**
	 * Flintstone is a simple file based key/value store that tasks can use
	 * to read and write to, so that a pre bootstrap task can signal to a post
	 * bootstrap task that it should perform some action.
	 *
	 * DO NOT store sensitive information in this store.
	 *
	 * @throws RuntimeException If we can't find a writable directory.
	 *
	 * @return void
	 */
	private function register_key_value_store(): void {
		$this->container->singleton(
			Flintstone::class,
			static function (): Flintstone {
				// DB path is: wp-content/solid_performance_updates.dat.
				// List of directories to try to write to, in order.
				return new Flintstone(
					'solid_performance_updates',
					[
						'dir' => rtrim( WP_CONTENT_DIR, '/\\' ) . '/',
					]
				);
			}
		);
	}

	/**
	 * Register the updater and the tasks that will run be pre/post boostrap.
	 *
	 * @return void
	 */
	private function register_updater(): void {
		$this->container->when( Advanced_Cache_Remover::class )
						->needs( '$version' )
						->give( static fn( $c ): string => $c->get( Core::PLUGIN_VERSION ) );

		$this->container->when( Advanced_Cache_Remover::class )
						->needs( '$destination' )
						->give( static fn( $c ): string => $c->get( Core::ADVANCED_CACHE_PATH ) );

		$this->container->when( Updater::class )
						->needs( '$pre_bootstrap_tasks' )
						->give(
							static fn( $c ): array => [
								// Add tasks that will run before WordPress is fully bootstrapped.
								// WARNING: Be careful with which WP functions you use, they may not be available.
								$c->get( Advanced_Cache_Remover::class ),
							]
						);

		$this->container->when( Updater::class )
						->needs( '$post_bootstrap_tasks' )
						->give(
							static fn( $c ): array => [
								// Add tasks that will run after WordPress is fully bootstrapped.
								$c->get( Advanced_Cache_Restorer::class ),
							]
						);

		// Ensure singleton for terminable shutdown task.
		$this->container->singleton( Updater::class, Updater::class );

		/*
		 * Attempt to run any pre bootstrap tasks BEFORE WordPress is fully bootstrapped.
		 *
		 * WARNING: This runs BEFORE WordPress is fully bootstrapped, be careful
		 * with the tasks you assign above, they must not rely on any WordPress functionality
		 * that doesn't exist when advanced-cache.php is loaded.
		 */
		$this->container->get( Updater::class )->run_pre_bootstrap_tasks();

		// Attempt to run any post bootstrap tasks, AFTER WordPress is fully bootstrapped.
		add_action( 'plugins_loaded', $this->container->callback( Updater::class, 'run_post_bootstrap_tasks' ) );
	}
}
