<?php
/**
 * The template used to output custom formatting for the welcome admin notice.
 *
 * @since 0.1.0
 *
 * @package SolidWP\Performance
 */

?>
<div class="icon-border">
	<img src="<?php echo esc_url( plugin_dir_url( SWPSP_PLUGIN_FILE ) . '/images/info.svg' ); ?>" alt="An info icon"/>
</div>
<div class="notice-content">
	<div class="notice-title">
		<h2><?php echo esc_html__( 'Welcome to Solid Performance!', 'solid-performance' ); ?></h2>
		<span class="notice-status">
			<img src="<?php echo esc_url( plugin_dir_url( SWPSP_PLUGIN_FILE ) . '/images/check.svg' ); ?>" alt="A green check mark"/>
			<?php echo esc_html__( 'Solid Performance activated', 'solid-performance' ); ?>
		</span>
	</div>
	<p><?php echo esc_html__( 'The page cache is enabled. Visit the Solid Performance settings page to customize your configuration.', 'solid-performance' ); ?></p>
</div>
<a class="settings-page-button" href="<?php echo esc_url( admin_url( 'options-general.php?page=' . \SolidWP\Performance\Admin\Settings_Page::MENU_SLUG ) ); ?>"><?php echo esc_html__( 'Go to settings', 'solid-performance' ); ?></a>
