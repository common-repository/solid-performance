<?php
/**
 * The Zstd compressor.
 *
 * @link https://github.com/facebook/zstd
 * @link https://github.com/kjdev/php-ext-zstd
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
 * The Zstd compressor.
 *
 * @link https://github.com/facebook/zstd
 * @link https://github.com/kjdev/php-ext-zstd
 *
 * @package SolidWP\Performance
 */
final class Zstd implements Compressible {

	use With_Headers;

	public const EXT = 'zstd';

	/**
	 * The zstd compression level.
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
	 * @param  int    $level The zstd compression level.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid compression level is passed.
	 */
	public function __construct( Config $config, int $level = 12 ) {
		if ( $level < 1 || $level > 22 ) {
			throw new InvalidArgumentException( 'Zstd compression level must be between -1 and 22' );
		}

		$this->config = $config;
		$this->level  = $level;
	}

	/**
	 * Whether zstd is supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return function_exists( 'zstd_compress' );
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
		$result = zstd_compress( $content, $this->level );

		if ( $result === false ) {
			throw new CompressionFailedException( 'Failed to compress content with zstd_compress' );
		}

		return $result;
	}
}
