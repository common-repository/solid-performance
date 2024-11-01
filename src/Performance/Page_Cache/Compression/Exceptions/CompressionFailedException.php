<?php
/**
 * Thrown when a compression strategy failed to compress its content.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Page_Cache\Compression\Exceptions;

use RuntimeException;

/**
 * Thrown when a compression strategy failed to compress its content.
 *
 * @package SolidWP\Performance
 */
final class CompressionFailedException extends RuntimeException {

}
