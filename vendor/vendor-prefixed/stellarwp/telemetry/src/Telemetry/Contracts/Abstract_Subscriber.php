<?php
/**
 * Handles setting up a base for all subscribers.
 *
 * @package SolidWP\Performance\StellarWP\Telemetry\Contracts
 *
 * @license GPL-2.0-or-later
 * Modified using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace SolidWP\Performance\StellarWP\Telemetry\Contracts;

use SolidWP\Performance\StellarWP\ContainerContract\ContainerInterface;

/**
 * Class Abstract_Subscriber
 *
 * @package SolidWP\Performance\StellarWP\Telemetry\Contracts
 */
abstract class Abstract_Subscriber implements Subscriber_Interface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor for the class.
	 *
	 * @param ContainerInterface $container The container.
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}
}
