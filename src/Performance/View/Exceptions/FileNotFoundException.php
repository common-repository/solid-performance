<?php
/**
 * Thrown when a view object can't find its view file.
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\View\Exceptions;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thrown when a view object can't find its view file.
 *
 * @package SolidWP\Performance
 */
final class FileNotFoundException extends Exception {

}
