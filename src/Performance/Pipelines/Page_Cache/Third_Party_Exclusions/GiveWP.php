<?php
/**
 * Prevents GiveWP specific pages from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 *
 * phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase
 */

namespace SolidWP\Performance\Pipelines\Page_Cache\Third_Party_Exclusions;

use SolidWP\Performance\Pipelines\Page_Cache\Pattern;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent GiveWP specific pages from being cached.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class GiveWP extends Pattern {

	/**
	 * {@inheritdoc}
	 */
	public function get_patterns(): array {
		if ( ! $this->is_givewp_active() ) {
			return [];
		}

		return [
			'/donations',
			'/donation-confirmation',
			'/donor-dashboard',
		];
	}

	/**
	 * Detects if GiveWP is currently active.
	 *
	 * @return boolean
	 */
	protected function is_givewp_active(): bool {
		return class_exists( 'Give' );
	}
}
