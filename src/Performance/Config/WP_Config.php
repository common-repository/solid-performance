<?php
/**
 * Handles all necessary logic for adjusting the wp-config.php.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Config;

use Exception;
use SolidWP\Performance\Notices\Notice;
use SolidWP\Performance\Notices\Notice_Handler;
use SolidWP_Performance_WPConfigTransformer as WPConfigTransformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides some helper functions related to the WP_Config
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class WP_Config {

	/**
	 * @var Notice_Handler
	 */
	private Notice_Handler $notice_handler;

	/**
	 * @param  Notice_Handler $notice_handler The notice handler.
	 */
	public function __construct( Notice_Handler $notice_handler ) {
		$this->notice_handler = $notice_handler;
	}

	/**
	 * Gets the full path of the site's wp-config.php file.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_wp_config_file_path(): string {
		$path = '';

		if ( getenv( 'WP_CONFIG_PATH' ) && file_exists( getenv( 'WP_CONFIG_PATH' ) ) ) {
			$path = getenv( 'WP_CONFIG_PATH' );
		} elseif ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			$path = ABSPATH . 'wp-config.php';
		} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			$path = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( $path ) {
			$path = realpath( $path );
		}

		/**
		 * Filter the config path.
		 *
		 * @since 0.1.0
		 *
		 * @param string $path Absolute path to the config file.
		 */
		return (string) apply_filters( 'solidwp/performance/get_wp_config_file_path', $path );
	}

	/**
	 * Adds the WP_CACHE constant if it's not already set or updates its value.
	 *
	 * The WP_CACHE constant configures WordPress to load the advanced-cache.php
	 * drop-in.
	 *
	 * @since 0.1.0
	 *
	 * @see https://github.com/wp-cli/wp-cli/blob/a339dca576df73c31af4b4d8054efc2dab9a0685/php/utils.php#L285-L310
	 * @see https://github.com/wp-cli/wp-config-transformer
	 *
	 * @param string $value The value to assign WP_CACHE (i.e. 'true', 'false').
	 *
	 * @return bool
	 */
	public function set_wp_cache( string $value ): bool {

		$config_transformer = $this->get_config_transformer();

		if ( $config_transformer === null ) {
			return false;
		}

		try {
			// Add WP_CACHE constant to the wp-config.php.
			$config_transformer->update(
				'constant',
				'WP_CACHE',
				$value,
				[
					'raw' => true,
				]
			);

		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
				$notice = new Notice(
					'error',
					'Unable to write changes to <code>wp-config.php</code>, please add <code>define( \'WP_CACHE\', true );</code> manually to enable page caching.',
					true
				);
				$this->notice_handler->add( $notice );
			}

			return false;
		}

		return true;
	}

	/**
	 * Gets the current state of the 'WP_CACHE' constant.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function get_wp_cache_status(): bool {
		if ( ! defined( 'WP_CACHE' ) ) {
			return false;
		}

		$config_transformer = $this->get_config_transformer();

		if ( $config_transformer === null ) {
			return false;
		}

		try {
			if ( ! $config_transformer->exists( 'constant', 'WP_CACHE' ) ) {
				return false;
			}

			$status = $config_transformer->get_value( 'constant', 'WP_CACHE' );

		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		return $status === 'true';
	}

	/**
	 * Undocumented function
	 *
	 * @return WPConfigTransformer|null
	 */
	protected function get_config_transformer(): ?WPConfigTransformer {
		try {
			$wp_config_transformer = new WPConfigTransformer( self::get_wp_config_file_path() );
		} catch ( Exception $e ) {
			$notice = new Notice( 'error', 'Unable to write changes to <code>wp-config.php</code>, please add <code>define( \'WP_CACHE\', true );</code> manually to enable page caching.', true );
			$this->notice_handler->add( $notice );

			return null;
		}

		return $wp_config_transformer;
	}
}
