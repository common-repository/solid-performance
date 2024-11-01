<?php
/**
 * The Permalink Data Transfer Object used with batch purging.
 *
 * @see     Batch_Purger
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Purge\Batch;

use InvalidArgumentException;
use SolidWP\Performance\Page_Cache\Purge\Enums\Purge_Strategy;

/**
 * The Permalink Data Transfer Object used with batch purging.
 *
 * @see     Batch_Purger
 *
 * @package SolidWP\Performance
 */
final class Permalink {

	/**
	 * The WP Object ID, e.g. post, term etc...
	 *
	 * @var int
	 */
	public int $object_id;

	/**
	 * The object's permalink.
	 *
	 * @var string
	 */
	public string $permalink;

	/**
	 * A valid purge strategy.
	 *
	 * @see Purge_Strategy
	 *
	 * @var string
	 */
	public string $purge_strategy;

	/**
	 * @param  int    $object_id       The WP Object ID, e.g. post, term etc...
	 * @param  string $permalink       The object's permalink.
	 * @param  string $purge_strategy  A valid purge strategy.
	 *
	 * @throws InvalidArgumentException If an invalid purge strategy is provided or if both object_id and permalink are empty.
	 */
	public function __construct( int $object_id = 0, string $permalink = '', string $purge_strategy = Purge_Strategy::PAGE ) {
		if ( empty( $object_id ) && empty( $permalink ) ) {
			throw new InvalidArgumentException( 'Both $object_id and $permalink cannot be empty' );
		}

		if ( ! Purge_Strategy::is_valid( $purge_strategy ) ) {
			throw new InvalidArgumentException( 'Invalid purge strategy: ' . $purge_strategy );
		}

		if ( empty( $object_id ) && $purge_strategy === Purge_Strategy::POST_ID ) {
			throw new InvalidArgumentException( sprintf( '$object_id must be provided with a %s purge strategy', Purge_Strategy::POST_ID ) );
		}

		$this->object_id      = $object_id;
		$this->permalink      = $permalink;
		$this->purge_strategy = $purge_strategy;
	}

	/**
	 * Acts as a static factory and returns a new Permalink object.
	 *
	 * @param  array{object_id: int, permalink: string, purge_strategy: string} $params  The parameters.
	 *
	 * @throws InvalidArgumentException If an invalid purge strategy is provided or if both object_id and permalink are empty.
	 *
	 * @return self
	 */
	public static function from( array $params ): self {
		return new self(
			intval( $params['object_id'] ?? 0 ),
			$params['permalink'] ?? '',
			$params['purge_strategy'] ?? Purge_Strategy::PAGE
		);
	}

	/**
	 * Generate a "unique" hash for this permalink for indexing purposes.
	 *
	 * @return string
	 */
	public function hash(): string {
		$algo = version_compare( PHP_VERSION, '8.1', '>=' ) ? 'xxh128' : 'md5';

		return hash( $algo, wp_json_encode( get_object_vars( $this ) ) );
	}
}
