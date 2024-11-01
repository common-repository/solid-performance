<?php
/**
 * A notice to display messages to users in the admin.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Notices;

use InvalidArgumentException;

/**
 * A Notice to display in the admin.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class Notice {

	public const INFO    = 'info';
	public const SUCCESS = 'success';
	public const WARNING = 'warning';
	public const ERROR   = 'error';

	public const ALLOWED_TYPES = [
		self::INFO,
		self::SUCCESS,
		self::WARNING,
		self::ERROR,
	];

	/**
	 * The notice type, one of the above constants.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * The already translated message to display.
	 *
	 * @since 0.1.0
	 *
	 * @see __()
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * Whether this notice is dismissible.
	 *
	 * @since 0.1.0
	 *
	 * @var bool
	 */
	private bool $dismissible;

	/**
	 * The ID of the notice.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Any additional classes that should be added to the notice.
	 *
	 * @since 0.1.0
	 *
	 * @var array<int,string>
	 */
	private array $additional_classes;

	/**
	 * Attributes (key value pairs) that should be added to the notice div.
	 *
	 * @since 0.1.0
	 *
	 * @var array<string,string>
	 */
	private array $attributes;

	/**
	 * Whether to wrap the message in paragraph tags.
	 *
	 * @since 0.1.0
	 *
	 * @var bool
	 */
	private bool $paragraph_wrap;

	/**
	 * Whether this is an alt-notice.
	 *
	 * @since 0.1.0
	 *
	 * @var bool
	 */
	private bool $alt;

	/**
	 * Whether this should be a large notice.
	 *
	 * @since 0.1.0
	 *
	 * @var bool
	 */
	private bool $large;

	/**
	 * The class constructor.
	 *
	 * @param string $type               The notice type, one of the above constants.
	 * @param string $message            The already translated message to display.
	 * @param bool   $dismissible        Whether this notice is dismissible.
	 * @param string $id                 The ID of the admin notice.
	 * @param array  $additional_classes Any additional classes that should be added to the notice.
	 * @param array  $attributes         An associative array of attributes and values added to the notice div.
	 * @param bool   $paragraph_wrap     If the message should be wrapped in paragraph tags.
	 * @param bool   $alt                Whether this is an alt-notice.
	 * @param bool   $large              Whether this should be a large notice.
	 *
	 * @throws InvalidArgumentException When an invalid type or message is used.
	 */
	public function __construct(
		string $type,
		string $message,
		bool $dismissible = false,
		string $id = '',
		array $additional_classes = [],
		array $attributes = [],
		bool $paragraph_wrap = true,
		bool $alt = false,
		bool $large = false
	) {
		if ( ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
			throw new InvalidArgumentException(
				sprintf(
					// Translators: a list of allowed values: info, success, warning, error.
					__( 'Notice $type must be one of: %s', 'solid-performance' ),
					implode( ', ', self::ALLOWED_TYPES )
				)
			);
		}

		if ( empty( $message ) ) {
			throw new InvalidArgumentException( __( 'The $message cannot be empty', 'solid-performance' ) );
		}

		$this->type               = $type;
		$this->message            = $message;
		$this->dismissible        = $dismissible;
		$this->id                 = $id;
		$this->additional_classes = $additional_classes;
		$this->attributes         = $attributes;
		$this->paragraph_wrap     = $paragraph_wrap;
		$this->alt                = $alt;
		$this->large              = $large;
	}

	/**
	 * Returns an array of the Notice's properties.
	 *
	 * @since 0.1.0
	 *
	 * @return array{type: string, message: string, dismissible: bool, alt: bool, large: bool}
	 */
	public function to_array(): array {
		return get_object_vars( $this );
	}
}
