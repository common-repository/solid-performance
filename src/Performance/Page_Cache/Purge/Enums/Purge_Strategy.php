<?php
/**
 * A pseudo-enum to provide the available purge strategies.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Enums;

/**
 * A pseudo-enum to provide the available purge strategies.
 *
 * @package SolidWP\Performance
 */
final class Purge_Strategy {

	public const POST_ID              = 'post_id';
	public const PAGE                 = 'page';
	public const PAGE_WITH_PAGINATION = 'page_with_pagination';

	/**
	 * Prevent instantiation of pseudo-enum.
	 */
	private function __construct() {
	}

	/**
	 * Validate if the provided strategy is valid.
	 *
	 * @param  string $strategy The strategy name.
	 *
	 * @return bool
	 */
	public static function is_valid( string $strategy ): bool {
		return in_array(
			$strategy,
			[
				self::POST_ID,
				self::PAGE,
				self::PAGE_WITH_PAGINATION,
			],
			true
		);
	}
}
