<?php
/**
 * Handles running all terminal/shutdown tasks..
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown;

use SolidWP\Performance\Shutdown\Contracts\Terminable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles running all terminal/shutdown tasks.
 *
 * @package SolidWP\Performance
 */
final class Shutdown_Handler {

	/**
	 * The collection of tasks to run on shutdown.
	 *
	 * @var Terminable[]
	 */
	private array $collection;

	/**
	 * @param  Terminable ...$collection The collection of tasks to run on shutdown.
	 */
	public function __construct( Terminable ...$collection ) {
		$this->collection = $collection;
	}

	/**
	 * If running on PHP-FPM, this will return the request, but continue processing
	 * any code after in the same thread, which means it instantly sends the request back
	 * to the browser without needing to wait for the code after it to process.
	 *
	 * Essentially, this is pseudo async/background processor.
	 *
	 * @action shutdown
	 *
	 * @return void
	 */
	public function handle(): void {
		// Return request early, if possible.
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} elseif ( function_exists( 'litespeed_finish_request' ) ) {
			litespeed_finish_request();
		}

		// Process all Terminable tasks.
		foreach ( $this->collection as $task ) {
			$task->terminate();
		}
	}
}
