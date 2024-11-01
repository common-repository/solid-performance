<?php
/**
 * The Brotli compression standard.
 *
 * @link https://github.com/kjdev/php-ext-brotli
 * @link https://github.com/google/brotli/
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
 * The Brotli compressor.
 *
 * @link https://github.com/kjdev/php-ext-brotli
 * @link https://github.com/google/brotli/
 *
 * @package SolidWP\Performance
 */
final class Brotli implements Compressible {

	use With_Headers;

	public const EXT = 'br';

	/**
	 * The brotli compression level.
	 *
	 * @var int
	 */
	private int $level;

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @param  Config $config The config object.
	 * @param  int    $level  The brotli compression level.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid compression level is passed.
	 */
	public function __construct( Config $config, int $level = 5 ) {
		if ( $level < - 1 || $level > 11 ) {
			throw new InvalidArgumentException( 'Brotli compression level must be between -1 and 11' );
		}

		$this->config = $config;
		$this->level  = $level;
	}

	/**
	 * Brotli requires SSL.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return is_ssl() && function_exists( 'brotli_compress' );
	}

	/**
	 * Brotli only works for https and requires a PHP extension.
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
		return $this->extension();
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
		$result = brotli_compress( $content, $this->level );

		if ( $result === false ) {
			throw new CompressionFailedException( 'Failed to compress content with brotli_compress' );
		}

		return $result;
	}
}
