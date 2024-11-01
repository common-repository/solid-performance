<?php
/**
 * Runs pre/post bootstrap tasks when the plugin is updated.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Update;

use SolidWP\Performance\Flintstone\Flintstone;
use SolidWP\Performance\Shutdown\Contracts\Terminable;
use SolidWP\Performance\Update\Tasks\Contracts\Task;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs pre/post bootstrap tasks when the plugin is updated.
 *
 * @package SolidWP\Performance
 */
final class Updater implements Terminable {

	/**
	 * Tasks that may run before WordPress is bootstrapped.
	 *
	 * @var Task[]
	 */
	private array $pre_bootstrap_tasks;

	/**
	 * Task that may run after WordPress is fully bootstrapped.
	 *
	 * @var Task[]
	 */
	private array $post_bootstrap_tasks;

	/**
	 * The flat file key/value store db.
	 *
	 * @var Flintstone
	 */
	private Flintstone $db;

	/**
	 * Whether we should flush the key/value store db.
	 *
	 * @var bool
	 */
	private bool $should_flush = false;

	/**
	 * @param  Task[]     $pre_bootstrap_tasks Tasks that may run before WordPress is bootstrapped.
	 * @param  Task[]     $post_bootstrap_tasks Task that may run after WordPress is fully bootstrapped.
	 * @param  Flintstone $db The flat file key/value store db.
	 */
	public function __construct( array $pre_bootstrap_tasks, array $post_bootstrap_tasks, Flintstone $db ) {
		$this->pre_bootstrap_tasks  = $pre_bootstrap_tasks;
		$this->post_bootstrap_tasks = $post_bootstrap_tasks;
		$this->db                   = $db;
	}

	/**
	 * Attempt to execute any pre bootstrap tasks.
	 *
	 * WARNING: This runs BEFORE WordPress is fully bootstrapped, so not all WP functions are available.
	 *
	 * @return void
	 */
	public function run_pre_bootstrap_tasks(): void {
		foreach ( $this->pre_bootstrap_tasks as $task ) {
			if ( ! $task->should_run() ) {
				continue;
			}

			$task->run();
		}
	}

	/**
	 * Attempt to execute any post bootstrap tasks.
	 *
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	public function run_post_bootstrap_tasks(): void {
		foreach ( $this->post_bootstrap_tasks as $task ) {
			if ( ! $task->should_run() ) {
				continue;
			}

			$this->should_flush = true;
			$task->run();
		}
	}
	/**
	 * Clear the key/value store if any of the post bootstrap tasks ran.
	 *
	 * @action shutdown
	 * @action solidwp/performance/terminate
	 *
	 * @return void
	 */
	public function terminate(): void {
		if ( ! $this->should_flush ) {
			return;
		}

		// Terminable shutdown runs twice, we only need this once.
		$this->should_flush = false;

		$this->db->flush();
	}
}
