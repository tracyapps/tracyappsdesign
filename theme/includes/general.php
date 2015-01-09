<?php
/**
 * General Theme-Specific Functions.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

add_action( 'init', 'tracyappsdesign_register_image_sizes', 5 );
/**
 * Register custom image sizes for the theme.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function tracyappsdesign_register_image_sizes() {
	// Set the 'post-thumbnail' size.
	set_post_thumbnail_size( 175, 130, true );

	// Add the 'tracyappsdesign-full' image size.
	add_image_size( 'tracyappsdesign-full', 1025, 500, true );
}

add_filter( 'excerpt_length', 'tracyappsdesign_excerpt_length' );
/**
 * Add a custom excerpt length.
 *
 * @since  1.0.0
 * @access public
 * @param  integer $length
 * @return integer
 */
function tracyappsdesign_excerpt_length( $length ) {
	return 60;
}

add_action( 'tha_entry_top', 'tracyappsdesign_do_sticky_banner' );
/**
 * Add markup for a sticky ribbon on sticky posts in archive views.
 *
 * @since   1.0.0
 * @return  void
 */
function tracyappsdesign_do_sticky_banner() {
	if ( is_singular() || ! is_sticky() ) {
		return;
	}
	?>
	<div class="corner-ribbon sticky">
		<p class="ribbon-content"><?php _e( 'Sticky', 'tracyappsdesign' ); ?></p>
	</div>
	<?php
}

/**
 * Display footer credits for the theme.
 *
 * @since      1.0.0
 * @return     void
 * @deprecated This was moved into the footer.php template and will be deleted.
 */
function tracyappsdesign_footer_creds() {}
