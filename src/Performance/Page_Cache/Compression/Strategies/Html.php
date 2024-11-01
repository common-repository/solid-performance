<?php
/**
 * The fallback compressor, which doesn't actually compress.
 *
 * This just returns uncompressed HTML.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression\Strategies;

use SolidWP\Performance\Page_Cache\Compression\Contracts\Compressible;

/**
 * The fallback compressor, which doesn't actually compress.
 *
 * This just returns uncompressed HTML.
 *
 * @package SolidWP\Performance
 */
final class Html implements Compressible {

	public const EXT = 'html';

	/**
	 * HTML is always supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return true;
	}

	/**
	 * HTML is always enabled.
	 *
	 * @return bool
	 */
	public function enabled(): bool {
		return true;
	}

	/**
	 * The file extension.
	 *
	 * @return string
	 */
	public function extension(): string {
		return self::EXT;
	}

	/**
	 * HTML has no specific content encoding.
	 *
	 * @return string
	 */
	public function encoding(): string {
		return '';
	}

	/**
	 * No additional headers required for HTML.
	 *
	 * @return void
	 */
	public function send_headers(): void {
	}

	/**
	 * Pass through the content without any compression.
	 *
	 * @param  string $content The content.
	 *
	 * @return string
	 */
	public function compress( string $content ): string {
		return $content;
	}
}
