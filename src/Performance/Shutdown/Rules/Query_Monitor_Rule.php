<?php
/**
 * If Query Monitor is collecting, it collects at shutdown priority 9,
 * so we need go above that to ensure we don't crash it.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Shutdown\Rules;

use SolidWP\Performance\Shutdown\Contracts\Priority_Rule;

/**
 * If Query Monitor is collecting, it collects at shutdown priority 9,
 * so we need go above that to ensure we don't crash it.
 *
 * @package SolidWP\Performance
 */
class Query_Monitor_Rule implements Priority_Rule {

	public const PRIORITY = 10;

	/**
	 * Return a priority of 10 if QM is collecting data.
	 *
	 * QM_VERSION is automatically defined by the plugin.
	 * QM_HIDE_SELF gets automatically defined if the plugin bootstraps.
	 *
	 * @return int|null
	 */
	public function get_priority(): ?int {
		$active = defined( 'QM_VERSION' ) && defined( 'QM_HIDE_SELF' );

		return $active ? self::PRIORITY : null;
	}
}
