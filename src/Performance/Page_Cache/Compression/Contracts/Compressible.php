<?php
/**
 * The Compressible contract for different browser compression strategies.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression\Contracts;

use SolidWP\Performance\Page_Cache\Compression\Exceptions\CompressionFailedException;

/**
 * @internal
 */
interface Compressible {

	/**
	 * Whether the current host has the appropriate PHP extensions to
	 * support this compression algorithm.
	 *
	 * @return bool
	 */
	public function supported(): bool;

	/**
	 * Whether this type of compression is enabled.
	 *
	 * @return bool
	 */
	public function enabled(): bool;

	/**
	 * Get the file extension for this type of compression.
	 *
	 * @return string
	 */
	public function extension(): string;

	/**
	 * Return the content-encoding header value, if any.
	 *
	 * @example gzip, br, zstd etc.
	 *
	 * @return string
	 */
	public function encoding(): string;

	/**
	 * Send Content-Encoding headers with the strategy's encoding.
	 *
	 * @return void
	 */
	public function send_headers(): void;

	/**
	 * Compress content.
	 *
	 * @param  string $content The content to compress.
	 *
	 * @throws CompressionFailedException When the strategy fails to compress the content.
	 *
	 * @return string
	 */
	public function compress( string $content ): string;
}
