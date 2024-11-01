<?php
/**
 * Handles all functionality related to excluding posts from cache via a meta box.
 *
 * @since 0.1.1
 *
 * @package SolidWP\Performance
 */

declare( strict_types=1 );

namespace SolidWP\Performance\Admin;

use WP_Post;

/**
 * Handles all functionality related to excluding posts from cache via a meta box.
 *
 * @since 0.1.1
 *
 * @package SolidWP\Performance
 */
final class Post_Cache_Exclusion {

	public const META_KEY = '_swpsp_post_exclude';

	/**
	 * An array of post type names to ignore, indexed by their post type name.
	 *
	 * @var array<string, bool>
	 */
	private array $post_types_to_ignore;

	/**
	 * @var string[]
	 */
	private array $post_types;

	/**
	 * @param  string[] $post_types  The available public post type objects.
	 * @param  string[] $post_types_to_ignore  Exclude the meta box from these post types.
	 */
	public function __construct( array $post_types, array $post_types_to_ignore ) {
		$this->post_types           = $post_types;
		$this->post_types_to_ignore = array_fill_keys( array_values( $post_types_to_ignore ), true );
	}

	/**
	 * Registers a meta key for posts.
	 *
	 * @action init
	 *
	 * @since 0.1.1
	 *
	 * @return void
	 */
	public static function register_meta(): void {
		register_post_meta(
			'', // Pass an empty string to register the meta key across all existing post types.
			self::META_KEY,
			[
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'boolean',
				'description'       => __( 'Exclude this post from the page cache.', 'solid-performance' ),
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);
	}

	/**
	 * Get the asset file produced by wp scripts.
	 *
	 * @param string $filepath the file path.
	 *
	 * @return array
	 */
	public function get_asset_file( string $filepath ): array {
		$plugin_path = realpath( plugin_dir_path( SWPSP_PLUGIN_FILE ) ) . DIRECTORY_SEPARATOR;
		$asset_path  = $plugin_path . $filepath . '.asset.php';
		return file_exists( $asset_path )
			? include $asset_path
			: [
				'dependencies' => [ 'wp-plugins', 'wp-edit-post', 'wp-element' ],
				'version'      => '0.1.1',
			];
	}

	/**
	 * Enqueue Script for Meta options.
	 *
	 * @action enqueue_block_editor_assets
	 */
	public function script_enqueue(): void {
		global $pagenow;

		if ( $pagenow === 'widgets.php' ) {
			return;
		}

		if ( is_customize_preview() ) {
			return;
		}

		if ( isset( $this->post_types_to_ignore[ get_post_type() ] ) ) {
			return;
		}

		// Enqueue the meta page scripts.
		wp_enqueue_script( 'solid-performance-meta' );
	}

	/**
	 * Register Script for Meta options.
	 *
	 * @action admin_init
	 */
	public function register_meta_script(): void {
		$script_meta = $this->get_asset_file( 'build/meta' );
		$url_path    = trailingslashit( plugin_dir_url( SWPSP_PLUGIN_FILE ) );
		wp_register_script( 'solid-performance-meta', $url_path . 'build/meta.js', $script_meta['dependencies'], $script_meta['version'], true );
		wp_set_script_translations( 'solid-performance-meta', 'solid-performance' );
	}

	/**
	 * Adds the meta box.
	 *
	 * @action load-post.php
	 * @action load-post-new.php
	 * @action add_meta_boxes
	 */
	public function add_metabox(): void {
		$post_types = [];

		foreach ( $this->post_types as $post_type ) {
			// Skip ignored post types.
			if ( isset( $this->post_types_to_ignore[ $post_type ] ) ) {
				continue;
			}

			$post_types[] = $post_type;
		}

		if ( ! $post_types ) {
			return;
		}

		add_meta_box(
			'_swpsp_classic_post_exclude',
			__( 'Cache Exclusion', 'solid-performance' ),
			[ $this, 'render_metabox' ],
			apply_filters( 'solidwp/performance/classic_meta_box_post_types', $post_types ),
			'side',
			'low',
			[
				'__back_compat_meta_box' => true,
			]
		);
	}

	/**
	 * Renders the meta box.
	 *
	 * @param WP_Post $post the post object.
	 */
	public function render_metabox( WP_Post $post ): void {
		// Add nonce for security and authentication.
		wp_nonce_field( 'swpsp_classic_meta_nonce_action', 'swpsp_classic_meta_nonce' );
		?>
		<div class="swpsp_classic_meta_boxes">
			<div class="swpsp_classic_meta_box" style="padding: 10px 0 0;">
				<div style="padding-bottom:10px;">
					<label for="<?php echo esc_attr( self::META_KEY ); ?>" style="font-weight: 600;">
						<input type="checkbox" id="<?php echo esc_attr( self::META_KEY ); ?>" name="<?php echo esc_attr( self::META_KEY ); ?>" value="1" <?php echo checked( get_post_meta( $post->ID, self::META_KEY, true ), '1', false ); ?> />
						<?php echo esc_html__( 'Exclude from Page Cache', 'solid-performance' ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves the meta box data.
	 *
	 * @action save_post
	 * @action load-post.php
	 * @action load-post-new.php
	 *
	 * @param int $post_id the post ID.
	 *
	 * @return void
	 */
	public function save_metabox( int $post_id ): void {
		// Check if our nonce is set.
		if ( ! isset( $_POST['swpsp_classic_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['swpsp_classic_meta_nonce'] ) );

		// Add nonce for security and authentication.
		if ( ! wp_verify_nonce( $nonce, 'swpsp_classic_meta_nonce_action' ) ) {
			return;
		}

		// Check if the current user has permission to edit the post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Update the meta field in the database.
		$meta_value = (int) isset( $_POST[ self::META_KEY ] );
		update_post_meta( $post_id, self::META_KEY, $meta_value );
	}
}
