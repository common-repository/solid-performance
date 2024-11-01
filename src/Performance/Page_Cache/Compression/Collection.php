<?php
/**
 * The collection of different compression strategies.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression;

use SolidWP\Performance\Page_Cache\Compression\Contracts\Compressible;
use SolidWP\Performance\Page_Cache\Compression\Strategies\Html;

/**
 * The collection of different compression strategies.
 *
 * @package SolidWP\Performance
 */
final class Collection {

	/**
	 * @var Compressible[]
	 */
	private array $compressors;

	/**
	 * Memoization cache for enabled compressors.
	 *
	 * @var Compressible[]|null
	 */
	private ?array $enabled;

	/**
	 * @param  Compressible[] $compressors The cache compression strategies.
	 */
	public function __construct( array $compressors ) {
		$this->compressors = $compressors;
	}

	/**
	 * Get all compressors.
	 *
	 * @return Compressible[]
	 */
	public function all(): array {
		return $this->compressors;
	}

	/**
	 * Get an enabled compressor based on the order the browser requested it, using the accept encoding header.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
	 *
	 * @param  string $header The accept-encoding header e.g. `gzip, compress, br, zstd`, `br;q=1.0, gzip;q=0.8, *;q=0.1`.
	 *
	 * @return Compressible We will always fall back to the raw HTML compressor if none are found.
	 */
	public function get_by_header( string $header ): Compressible {
		$algorithms = explode( ',', $header );
		$found      = [];

		foreach ( $algorithms as $algorithm ) {
			$algorithm = trim( $algorithm );
			$weight    = 1.0;

			// This header contains weighted algorithms.
			if ( str_contains( $algorithm, ';q=' ) ) {
				$weight    = (float) str_replace( ';q=', '', strstr( $algorithm, ';q=' ) );
				$algorithm = strtok( $algorithm, ';q=' );
			}

			$compressor = $this->get_by_encoding( $algorithm );

			if ( ! $compressor ) {
				continue;
			}

			$found[] = [
				'compressor' => $compressor,
				'weight'     => $weight,
			];
		}

		// If no compression strategies are found, just return the raw HTML strategy.
		if ( ! $found ) {
			return $this->get_by_extension( Html::EXT );
		}

		// Sort algorithms by their weight.
		usort( $found, static fn( array $a, array $b ) => $b['weight'] <=> $a['weight'] );

		return $found[0]['compressor'];
	}

	/**
	 * Get an enabled compressor by encoding type.
	 *
	 * @param  string $encoding The content encoding, e.g. gzip, br, zstd etc...
	 *
	 * @return Compressible|null
	 */
	public function get_by_encoding( string $encoding ): ?Compressible {
		if ( ! $encoding ) {
			return null;
		}

		foreach ( $this->enabled() as $compressor ) {
			if ( $compressor->encoding() === $encoding ) {
				return $compressor;
			}
		}

		return null;
	}

	/**
	 * Get an enabled compressor by file extension.
	 *
	 * @param  string $extension The file extension, e.g. gz, br, html etc...
	 *
	 * @return Compressible|null
	 */
	public function get_by_extension( string $extension ): ?Compressible {
		if ( ! $extension ) {
			return null;
		}

		foreach ( $this->enabled() as $compressor ) {
			if ( $compressor->extension() === $extension ) {
				return $compressor;
			}
		}

		return null;
	}

	/**
	 * Get all enabled compressors.
	 *
	 * @return Compressible[]
	 */
	public function enabled(): array {
		if ( isset( $this->enabled ) ) {
			return $this->enabled;
		}

		$this->enabled = array_filter( $this->compressors, static fn( Compressible $c ): bool => $c->enabled() );

		return $this->enabled;
	}
}
