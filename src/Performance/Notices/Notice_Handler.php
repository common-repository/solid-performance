<?php
/**
 * A class to handle rendering notices in the admin.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Notices;

/**
 * A handler for rendering notices.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
final class Notice_Handler {

	public const TRANSIENT = 'solid_performance_notices';

	/**
	 * An array of notices.
	 *
	 * @since 0.1.0
	 *
	 * @var Notice[]
	 */
	private array $notices;

	/**
	 * The class constructor.
	 */
	public function __construct() {
		$this->notices = $this->all();
	}

	/**
	 * Add a notice to display.
	 *
	 * @since 0.1.0
	 *
	 * @param Notice $notice The notice to add.
	 *
	 * @return void
	 */
	public function add( Notice $notice ): void {
		$this->notices = array_merge( $this->all(), [ $notice ] );
		$this->save();
	}

	/**
	 * Display all notices and then clear them.
	 *
	 * @since 0.1.0
	 *
	 * @action admin_notices
	 *
	 * @return void
	 */
	public function display(): void {
		if ( count( $this->notices ) <= 0 ) {
			return;
		}

		foreach ( $this->notices as $notice ) {
			$args = $notice->to_array();

			$classes = [
				$args['alt'] ? 'notice-alt' : '',
				$args['large'] ? 'notice-large' : '',
			];

			unset( $args['alt'] );
			unset( $args['large'] );

			// Remove any empty class values.
			$args['classes'] = array_filter( $classes );

			wp_admin_notice( $args['message'], $args );
		}

		$this->clear();
	}

	/**
	 * Clear all notices.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function clear(): bool {
		$this->notices = [];

		return (bool) delete_transient( self::TRANSIENT );
	}

	/**
	 * Get all notices.
	 *
	 * @since 0.1.0
	 *
	 * @return Notice[]
	 */
	private function all(): array {
		return array_filter( (array) get_transient( self::TRANSIENT ) );
	}

	/**
	 * Save the existing state of notices.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	private function save(): bool {
		return (bool) set_transient( self::TRANSIENT, $this->notices, 300 );
	}
}
