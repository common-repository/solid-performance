<?php
/**
 * The Gzip compressor (using gzencode).
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression\Strategies;

use InvalidArgumentException;
use SolidWP\Performance\Config\Config;
use SolidWP\Performance\Page_Cache\Compression\Contracts\Compressible;
use SolidWP\Performance\Page_Cache\Compression\Exceptions\CompressionFailedException;
use SolidWP\Performance\Page_Cache\Compression\Traits\With_Headers;

/**
 * The Gzip compressor (using gzencode).
 *
 * @package SolidWP\Performance
 */
final class Gzip implements Compressible {

	use With_Headers;

	public const EXT = 'gz';

	/**
	 * The gzip compression level.
	 *
	 * @var int
	 */
	private int $level;

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @param Config $config The config object.
	 * @param  int    $level The gzip compression level.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid compression level is passed.
	 */
	public function __construct( Config $config, int $level = 6 ) {
		if ( $level < - 1 || $level > 9 ) {
			throw new InvalidArgumentException( 'Gzip compression level must be between -1 and 9' );
		}

		$this->config = $config;
		$this->level  = $level;
	}

	/**
	 * Whether gzip is supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return function_exists( 'gzencode' );
	}

	/**
	 * Whether this compressor is enabled.
	 *
	 * @return bool
	 */
	public function enabled(): bool {
		return $this->supported() && $this->config->get( 'page_cache.compression.enabled' );
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
	 * The content encoding header value.
	 *
	 * @return string
	 */
	public function encoding(): string {
		return 'gzip';
	}

	/**
	 * Compress the content.
	 *
	 * @param  string $content The content to compress.
	 *
	 * @throws CompressionFailedException When the strategy fails to compress the content.
	 *
	 * @return string
	 */
	public function compress( string $content ): string {
		$result = gzencode( $content, $this->level );

		if ( $result === false ) {
			throw new CompressionFailedException( 'Failed to compress content with gzencode' );
		}

		return $result;
	}
}
