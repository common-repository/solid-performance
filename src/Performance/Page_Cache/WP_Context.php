<?php
/**
 * A class for storing the current context for a request in WordPress.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

namespace SolidWP\Performance\Page_Cache;

use SolidWP\Performance\Http\Request;

/**
 * A class for storing the current context for a request in WordPress.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */
class WP_Context {
	/**
	 * The current request.
	 *
	 * @since 0.1.0
	 *
	 * @var Request
	 */
	private Request $request;

	/**
	 * The current post.
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	private int $post_id;

	/**
	 * The class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Request $request A request instance.
	 * @param int     $post_id The ID of the current post.
	 */
	public function __construct( Request $request, int $post_id = 0 ) {
		$this->request = $request;
		$this->post_id = $post_id;
	}

	/**
	 * Gets the current request URI.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_request(): Request {
		return $this->request;
	}

	/**
	 * Gets the current post ID.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	public function get_post_id(): int {
		return $this->post_id;
	}
}
