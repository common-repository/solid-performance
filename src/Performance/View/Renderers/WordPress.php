<?php
/**
 * The WordPress View Renderer.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\View\Renderers;

use SolidWP\Performance\View\Contracts\View;
use SolidWP\Performance\View\Exceptions\FileNotFoundException;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WordPress View Renderer.
 *
 * @package SolidWP\Performance
 */
final class WordPress implements View {

	/**
	 * The server path to the views folder.
	 *
	 * @var string
	 *
	 * @example /app/views/
	 */
	private string $directory;

	/**
	 * The file extension of view files.
	 *
	 * @var string
	 *
	 * @example .php
	 */
	private string $extension;

	/**
	 * @param  string $directory The server path to the views folder.
	 * @param  string $extension The file extension of view files.
	 */
	public function __construct( string $directory, string $extension = '.php' ) {
		$this->directory = rtrim( realpath( $directory ), '/\\' ) . '/';
		$this->extension = $extension;
	}

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
	 * @throws FileNotFoundException|Throwable If the view file cannot be found.
	 *
	 * @return void
	 */
	public function render( string $name, array $args = [] ): void {
		echo $this->render_to_string( $name, $args );
	}

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
	 * @throws FileNotFoundException|Throwable If the view file cannot be found.
	 *
	 * @return string
	 */
	public function render_to_string( string $name, array $args = [] ): string {
		$file = $this->get_path( $name );

		try {
			$level = ob_get_level();
			ob_start();

			extract( $args );
			include $file;

			return (string) ob_get_clean();
		} catch ( Throwable $e ) {
			while ( ob_get_level() > $level ) {
				ob_end_clean();
			}

			throw $e;
		}
	}

	/**
	 * Get the absolute server path to a view file.
	 *
	 * @param  string $name  The relative view path/name, e.g. `admin/notice`.
	 *
	 * @throws FileNotFoundException If the view file cannot be found.
	 *
	 * @return string The absolute path to the view file.
	 */
	private function get_path( string $name ): string {
		$file = $this->directory . $name . $this->extension;
		$path = realpath( $file );

		if ( $path === false ) {
			throw new FileNotFoundException(
				sprintf( 'View file "%s" not found or not readable.', $file )
			);
		}

		return $path;
	}
}
