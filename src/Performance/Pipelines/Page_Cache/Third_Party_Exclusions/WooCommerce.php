<?php
/**
 * Prevent requests from being saved to the cache when matching user-defined exclusion patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP|Performance
 *
 * phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase
 */

namespace SolidWP\Performance\Pipelines\Page_Cache\Third_Party_Exclusions;

use Closure;
use SolidWP\Performance\StellarWP\Pipeline\Contracts\Pipe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent caching requests that match user-defined patterns.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class WooCommerce implements Pipe {

	/**
	 * {@inheritdoc}
	 */
	public function handle( $context, Closure $next ) {

		if ( ! $this->is_woocommerce_active() ) {
			return $next( $context );
		}

		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return false;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return false;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return false;
		}

		return $next( $context );
	}

	/**
	 * Determines if WooCommerce is currently active.
	 *
	 * @since 0.1.0
	 *
	 * @return boolean
	 */
	public function is_woocommerce_active(): bool {
		return class_exists( 'WooCommerce' );
	}
}
