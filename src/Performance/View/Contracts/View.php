<?php
/**
 * The view contract.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\View\Contracts;

use SolidWP\Performance\View\Exceptions\FileNotFoundException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The view contract.
 *
 * @internal
 *
 * @package SolidWP\Performance
 */
interface View {

	/**
	 * Renders a view.
	 *
	 * @example If the server path is /app/views, and you wish to load /app/views/admin/notice.php,
	 * pass `admin/notice` as the view name.
	 *
	 * @param  string  $name  The relative path/name of the view file without extension.
	 *
	 * @param  mixed[] $args  Arguments to be extracted and passed to the view.
	 *
	 * @throws FileNotFoundException If the view file cannot be found.
	 *
	 * @return void
	 */
	public function render( string $name, array $args = [] ): void;

	/**
	 * Renders a view and returns it as a string to be echoed.
	 *
	 * @example If the server path is /app/views, and you wish to load /app/views/admin/notice.php,
	 * pass `admin/notice` as the view name.
	 *
	 * @param  string  $name  The relative path/name of the view file without extension.
	 *
	 * @param  mixed[] $args  Arguments to be extracted and passed to the view.
	 *
	 * @throws FileNotFoundException If the view file cannot be found.
	 *
	 * @return string
	 */
	public function render_to_string( string $name, array $args = [] ): string;
}
