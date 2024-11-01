<?php
/**
 * The Updater Task contract. Both pre/post bootstrap tasks should
 * implement this interface.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Update\Tasks\Contracts;

interface Task {

	/**
	 * Whether this task should run.
	 *
	 * @return bool
	 */
	public function should_run(): bool;

	/**
	 * Run the task.
	 *
	 * @return void
	 */
	public function run(): void;
}
